# MAC Core

MAC Core is the internal core plugin used across Circea WordPress projects.

It provides shared functionality, conventions, and infrastructure that should not live in themes or client-specific plugins.

## Purpose

- Centralize reusable functionality
- Enforce consistent standards across projects
- Reduce duplication between client builds
- Serve as a foundation for future internal tooling

## Scope

This plugin is:
- Intended for agency and client use
- Not a general-purpose WordPress plugin
- Actively developed alongside Circea projects

## Development Workflow

- `develop` is the default development branch
- `main` will track stable releases
- Releases are created from `main`
- Versions follow GitHub Releases and the plugin header version

## Updates

Plugin updates are delivered via GitHub Releases.
Client sites receive updates through the WordPress dashboard.

## Requirements

- WordPress 6.0+
- PHP 8.0+

## License

GPL v2. See the `LICENSE` file for details.
