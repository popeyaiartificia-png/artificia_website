# Editing the Artificia WordPress site

Sign in at https://artificiaai.com/wp-admin/. The owner has the private login file on this laptop; passwords are never stored in Git.

## Pages and sections

1. Open **Pages**, choose the page, then **Edit**.
2. Select an Artificia section and edit its named fields. Open **List View** to select or reorder sections.
3. Use the image picker to choose a Media Library image. Keep useful alt text.
4. Open **Preview**, check desktop and mobile, then **Update**. WordPress revisions retain earlier saves.

The theme owns the layout, fonts, animation and section markup. Edit the fields rather than replacing sections with generic layout blocks.

## SEO

The editor sidebar's **Page heading and SEO** panel contains the H1, search title, description, canonical URL, noindex flag, share image and schema fields. Keep existing addresses and canonical URLs unless an address change is intentional and has a redirect. The imported SEO fields feed the original site's SEO renderer, retaining its public markup.

## Blog and images

Use **Posts** for blog articles, with Artificia sections, excerpt, featured image, categories and tags. Posts use `/blog/{slug}`. Use **Media** for images and alt text. Imported image addresses remain `/uploads/...`.

## Enquiries

Open **Leads**, choose a form filter and open a record. Flag unwanted submissions as spam. Administrators can export CSV, resend a record to n8n and open an attached resume. Applications are kept separate from sales conversion tracking.

## Redirects

Administrators open **Tools > Redirects** to add or edit an old address and its destination. Choose Exact for one address, Prefix for the whole path, and 301 for a permanent move. Existing pages cannot be hidden by a redirect, and loops are rejected.

## Site settings

Administrators open **Settings > Artificia** for business/contact details, tracking IDs, alert delivery, n8n, robots.txt and llms.txt. Keep secret tokens private. Existing delivery settings are imported; n8n still depends on the owner's laptop/Tailscale service being available.

## Hosting

Do not use Hostinger's static deployment or re-enable root replacement auto-deployment. WordPress files, uploads, its database and private resumes must survive code updates. The deployment and rollback procedures are in [wordpress-migration.md](wordpress-migration.md).
