# blubby!

## Run the mobile app on iOS and Android

Install Expo Go compatible with the project's SDK on your phone. From the repository root, start the Expo development server with a tunnel so the phone does not need to use the local network:

```sh
cd mobile
EXPO_UNSTABLE_TUNNEL_V2=1 npx expo start --tunnel -c
```

Scan the terminal QR code with Expo Go, then open the project. On iOS, the Camera app can open the QR code in Expo Go.

## Run the backend

See [backend setup](backend/README.md) for the Laravel API and PostgreSQL development environment.
