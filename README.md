# Speed Builder

This repository contains two related deliverables:

- `wordpress-plugin/speed-builder/` — the WordPress-native **Speed Builder Phase 1** plugin source.
- The existing Vite marketing/prototype application in `src/`.

## WordPress plugin

1. Zip the `wordpress-plugin/speed-builder` directory, or use the included `release/speed-builder-phase-1.zip` package.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Activate **Speed Builder**.
4. Open **Speed Builder → Pages**, create a page, and choose **Create and open builder**.

The Phase 1 plugin uses ordinary WordPress pages and stores a versioned JSON document in the `_speed_builder_data` post meta field.
