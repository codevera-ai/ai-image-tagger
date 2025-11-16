# AI Image Tagger

Automatically generate titles, descriptions, and tags for your media library images using artificial intelligence. Choose from OpenAI GPT-4o, Anthropic Claude, or Google Gemini to save time and improve your image metadata.

## Requirements

Before installing, make sure your WordPress site meets these requirements:
- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB: 10.3 or higher
- **HTTPS**: Required for secure API communication

## Installation

### Method 1: Upload via WordPress admin (recommended)

1. Download the latest release ZIP file
2. Log in to your WordPress admin panel
3. Go to **Plugins → Add new**
4. Click **Upload plugin**
5. Choose the ZIP file you downloaded
6. Click **Install now**
7. Once installed, click **Activate plugin**

### Method 2: Manual upload via FTP

1. Download the latest release ZIP file
2. Extract the ZIP file on your computer
3. Upload the `ai-image-tagger` folder to your `/wp-content/plugins/` directory using FTP
4. Log in to your WordPress admin panel
5. Go to **Plugins**
6. Find "AI Image Tagger" and click **Activate**

## Getting started

### Step 1: Get an API key

You need an API key from at least one AI provider. Choose from:

- **OpenAI**: Sign up at https://platform.openai.com/api-keys (costs approximately £0.002 per image)
- **Claude**: Sign up at https://console.anthropic.com/settings/keys (costs approximately £0.003 per image)
- **Gemini**: Sign up at https://makersuite.google.com/app/apikey (costs approximately £0.001 per image)

### Step 2: Configure the plugin

1. In your WordPress admin, go to **Settings → AI Image Tagger**
2. Enter your API key(s)
3. Choose your default AI provider
4. Configure these options:
   - **Automatic processing**: Process images automatically when you upload them
   - **Maximum tags**: How many tags to generate per image (recommended: 5-10)

### Step 3: Start using it

## What can it do?

- **Generate titles**: Create descriptive, SEO-friendly titles for your images
- **Write descriptions**: Automatically write detailed alt text and descriptions
- **Add tags**: Generate relevant tags to help organise your media library
- **Process automatically**: Set it up once and new uploads get processed automatically
- **Multiple languages**: Respects your WordPress language settings
- **Search enhancement**: AI metadata is included in WordPress search results
- **Secure storage**: API keys are encrypted for security

## How to use

### Process new images automatically

1. Go to **Settings → AI Image Tagger**
2. Enable **"Automatically process images on upload"**
3. Upload images as normal - they'll be queued for AI processing automatically
4. The plugin processes images in the background using WordPress cron

### Process existing images in bulk

1. Go to **Media → Library**
2. Switch to **List view**
3. Select the images you want to process (tick the checkboxes)
4. From the **Bulk actions** dropdown, choose **"Generate AI metadata"**
5. Click **Apply**
6. The images will be queued for processing

### Process a single image

1. Go to **Media → Library**
2. Click on an image to edit it
3. Look for the **"AI-generated metadata"** box on the right side
4. Click **"Generate metadata"** (or **"Regenerate metadata"** if already processed)
5. Wait a few moments for the AI to process your image
6. The generated title, description, and tags will appear

## Frequently asked questions

### How much does it cost to use?

The plugin itself is free, but you'll need to pay for API usage from your chosen AI provider. Costs are approximately:
- OpenAI (GPT-4o): £0.002 per image
- Claude (3.5 Sonnet): £0.003 per image
- Gemini (1.5 Pro): £0.001 per image

For example, processing 1,000 images with Gemini would cost around £1.

### Can I use more than one AI provider?

Yes. You can configure multiple API keys and switch between providers at any time. This is useful if you want to compare results or if one provider is temporarily unavailable.

### Will it overwrite my existing image metadata?

Yes. When you process an image, the AI generates new metadata and overwrites your existing titles, descriptions, and tags. Make sure you want to replace your current metadata before processing.

### Does it work with all image types?

The plugin works with standard WordPress image types (JPEG, PNG, GIF, WebP). The AI providers analyse the visual content of your images to generate appropriate metadata.

### Will it slow down my website?

No. Image processing happens in the background using WordPress's built-in cron system. Visitors to your website won't experience any slowdown.

## Troubleshooting

### Images aren't being processed

If your images aren't being processed automatically, try these steps:

1. **Check WordPress cron**: Some hosting providers disable WordPress cron. Ask your hosting provider if WP-Cron is enabled
2. **Verify API keys**: Go to **Settings → AI Image Tagger** and make sure your API keys are entered correctly
3. **Check the logs**: Go to **AI Image Tagger → Processing log** to see if there are any error messages

### I get API errors

If you're seeing API errors in the processing log:

1. **Verify your API key**: Make sure you've copied the entire API key correctly with no extra spaces
2. **Check your API credits**: Log in to your AI provider account and verify you have available credits
3. **Ensure HTTPS is enabled**: The plugin requires your site to use HTTPS for secure API communication
4. **Check rate limits**: Some AI providers have rate limits. If you're processing many images, try reducing the batch size in settings

### The plugin won't activate

If the plugin won't activate:

1. **Check PHP version**: Make sure your server is running PHP 8.0 or higher
2. **Check WordPress version**: Make sure you're running WordPress 6.0 or higher
3. **View error messages**: Look for error messages when you try to activate the plugin
4. **Contact support**: If you can't resolve the issue, contact your hosting provider

### Generated metadata isn't what I expected

If the AI-generated metadata doesn't match your expectations:

1. **Try a different provider**: Different AI providers can produce different results
2. **Regenerate**: Click "Regenerate metadata" to get a fresh result - AI can produce different outputs each time

## Privacy and security

- Your API keys are encrypted before being stored in your WordPress database
- All communication with AI providers uses HTTPS encryption
- Images are sent directly to the AI provider and are not stored by them (according to each provider's data policy)
- No data is sent to any third parties except the AI provider you choose
- Only WordPress users with appropriate permissions can access the plugin settings and processing features

## Need help?

If you're having trouble with the plugin or have questions, please leave an issue on the GitHub repository.

## Licence

This plugin is free software released under the GPL v2 licence. You can use it on as many websites as you like at no cost.
