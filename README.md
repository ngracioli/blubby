# blubby!

## Run the full app on iOS and Expo Web

Install Expo Go compatible with the project's SDK on your phone. Start Docker, then run this from the repository root:

```sh
cd mobile
npm run dev:tunnel
```

The command starts the Sail backend, creates a temporary HTTPS tunnel for the API, and starts Expo with its own tunnel. It supplies the API URL to the app automatically. Scan the Expo QR code with Expo Go or open the Web URL printed by Expo. Press Ctrl+C to stop Expo, the API tunnel, and Sail services.

The first run downloads the pinned Cloudflare Tunnel binary if `cloudflared` is not installed. The API tunnel address changes each time and anyone with its URL can reach the development backend, so use this for development only. See [backend setup](backend/README.md) for the Laravel API and PostgreSQL environment.
