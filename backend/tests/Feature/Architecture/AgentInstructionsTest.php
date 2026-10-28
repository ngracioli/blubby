<?php

namespace Tests\Feature\Architecture;

use Laravel\Boost\Install\Agents\Agent;
use Laravel\Boost\Install\GuidelineComposer;
use Laravel\Boost\Install\SkillComposer;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class AgentInstructionsTest extends TestCase
{
    public function test_boost_discovers_and_installs_the_selected_project_skills(): void
    {
        $selection = json_decode(file_get_contents(base_path('boost.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertTrue($selection['guidelines']);
        $this->assertNotEmpty($selection['agents']);
        $skills = app(SkillComposer::class)->skills();

        foreach (['backend-development', 'backend-review', 'boost-maintenance'] as $name) {
            $this->assertContains($name, $selection['skills']);
            $skill = $skills->get($name);
            $this->assertNotNull($skill, $name);
            $this->assertTrue($skill->custom);
            $this->assertNotEmpty($skill->description);
            $source = base_path(".ai/skills/{$name}/SKILL.md");
            $this->assertSame(realpath(dirname($source)), realpath($skill->path));

            foreach ($selection['agents'] as $agent) {
                $client = Agent::fromName($agent);
                $this->assertNotNull($client, $agent);
                $installed = base_path($client->skillsPath()."/{$name}/SKILL.md");
                $this->assertFileIsReadable($installed, $agent);
                $this->assertSame(file_get_contents($source), file_get_contents($installed), $agent);
                $this->assertSame('AGENTS.md', $client->guidelinesPath(), $agent);
            }
        }

        $this->assertStringContainsString('architectural', $skills->get('backend-review')->description);
        $this->assertStringContainsString('compliance', $skills->get('backend-review')->description);
    }

    public function test_the_generated_entry_point_contains_the_canonical_guideline(): void
    {
        $guidelines = app(GuidelineComposer::class)->withoutRuleExtraction()->guidelines();
        $project = $guidelines->get('.ai/project-architecture');
        $this->assertNotNull($project);
        $this->assertTrue($project['custom']);
        $entry = file_get_contents(base_path('AGENTS.md'));
        $this->assertSame(1, substr_count($entry, '<laravel-boost-guidelines>'));
        $this->assertSame(1, substr_count($entry, '</laravel-boost-guidelines>'));
        $rendered = GuidelineComposer::composeGuidelines(collect(['.ai/project-architecture' => $project]));
        $this->assertStringContainsString($rendered, $entry);
        $this->assertSame('@AGENTS.md', trim(file_get_contents(base_path('CLAUDE.md'))));

        foreach (['docs/architecture/overview.md', 'docs/architecture/naming.md', 'docs/architecture/design-patterns.md', 'docs/architecture/conventions.md', 'docs/agent-workflow.md', 'backend-development', 'backend-review', 'boost-maintenance'] as $reference) {
            $this->assertStringContainsString($reference, $project['content']);
        }
    }

    public function test_backend_documentation_and_skill_file_links_resolve(): void
    {
        $files = Finder::create()->files()->name('*.md')->in([base_path('docs'), base_path('.ai')]);

        foreach ($files as $file) {
            preg_match_all('/\[[^\]]*\]\(([^\s)]+)(?:\s+"[^"]*")?\)/', $file->getContents(), $matches);

            foreach ($matches[1] as $target) {
                if (preg_match('/^(?:[a-z][a-z0-9+.-]*:|#)/i', $target)) {
                    continue;
                }

                $path = rawurldecode(explode('#', $target, 2)[0]);
                // Sail mounts only backend; repository-level references need a host check.
                $segments = [];
                foreach (explode('/', $file->getPath().'/'.$path) as $segment) {
                    if ($segment === '..') {
                        array_pop($segments);
                    } elseif ($segment !== '.' && $segment !== '') {
                        $segments[] = $segment;
                    }
                }
                if (! str_starts_with('/'.implode('/', $segments), base_path().'/')) {
                    continue;
                }

                $this->assertFileExists($file->getPath().'/'.$path, $file->getRelativePathname().': '.$target);
            }
        }
    }

    public function test_application_namespaces_follow_the_existing_psr4_mapping(): void
    {
        foreach (Finder::create()->files()->name('*.php')->in(app_path()) as $file) {
            preg_match('/^namespace\s+([^;]+);/m', $file->getContents(), $match);
            $directory = str_replace('/', '\\', $file->getRelativePath());
            $this->assertSame('App'.($directory === '' ? '' : '\\'.$directory), $match[1] ?? null, $file->getRelativePathname());
        }

        foreach (['Actions', 'Services', 'Queries'] as $directory) {
            $this->assertDirectoryDoesNotExist(app_path($directory), 'Business code belongs to its domain; change the documented decision before changing this gate.');
        }
    }
}
