# Documentation Site

This directory contains the documentation for the Espresso PHP Router.

## Generating the Site

To generate and view the documentation site locally:

1. Install MkDocs (requires Python):
   ```bash
   pip install mkdocs
   ```

2. Serve the site locally:
   ```bash
   mkdocs serve
   ```

3. Open your browser to `http://localhost:8000` to view the site.

## Building for Production

To build the static site for deployment:

```bash
mkdocs build
```

The built site will be in the `site/` directory.