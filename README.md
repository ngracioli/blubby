# blubby!

## Run the mobile app on iOS

Install Expo Go SDK 58 on your iPhone. From the repository root, start the Expo development server with a tunnel so your phone does not need to use the local network:

```sh
cd mobile
EXPO_UNSTABLE_TUNNEL_V2=1 npx expo start --tunnel -c
```

Scan the QR code shown in the terminal with the iPhone camera, then open the link in Expo Go.
