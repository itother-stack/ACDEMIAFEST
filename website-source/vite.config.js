import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// Local development: while running `npm run dev`, forward /api, /register, /admin and /storage
// to the PHP backend started with `php -S localhost:8000` inside public_html
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      "/api": "http://localhost:8000",
      "/register": "http://localhost:8000",
      "/admin": "http://localhost:8000",
      "/storage": "http://localhost:8000",
    },
  },
});
