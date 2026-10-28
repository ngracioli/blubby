---
name: boost-maintenance
description: Maintain Blubby's custom Laravel Boost guidelines and skills, inspect installed-version behavior and safely regenerate configured agent outputs. Use for Boost instruction updates or discovery problems; not dependency upgrades or feature implementation.
---

# Boost maintenance

Read [agent workflow](../../../docs/agent-workflow.md), especially ownership, client discovery and regeneration. Commands run from `backend/` through Sail. An instruction refresh does not authorize a Composer update, MCP reinstallation or database changes.

1. Inspect the working tree, `boost.json`, `config/boost.php`, agent files and `.ai` sources. Confirm installed Boost/framework versions using lockfiles and available Boost information. Check `boost:update --help` and installed command/writer behavior if the version changed. Inventory affected destinations and preserve existing custom content before regeneration.
2. Edit canonical project sources, never official generated copies. Use a distinct `.ai/guidelines` name to complement native rules. Keep stable rules in canonical agent instructions/docs; skills route to those owners rather than duplicate them. Use `.ai/skills/<name>/SKILL.md` with matching name/description frontmatter. Avoid overriding official guideline or skill names unless explicitly requested.
3. For the existing selection, use `./vendor/bin/sail artisan boost:update --no-discover --no-interaction`. Do not use `--ignore-skills` when changing skills. If selecting a newly installed package is actually requested, run discovery deliberately and review its selection; noninteractive execution does not automatically opt into new packages. This command regenerates guidance, not Composer dependencies.
4. Check the diff, generated marker uniqueness, unchanged `CLAUDE.md` import, sources, skill frontmatter and links in every configured target. Check official skill content for unintended changes; the writer can replace generated directories. Confirm MCP configuration remains intact. Rerun the same update and confirm no further changes before reporting regeneration as stable.
5. Report exact versions, command results, files affected and validation limits. If Sail/MCP cannot run, complete safe source edits but report regeneration/discovery as unverified. Never fabricate generated outputs or claim client activation without evidence.
