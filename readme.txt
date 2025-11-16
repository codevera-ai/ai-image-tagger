=== AI Image Tagger ===
Contributors: codevera
Tags: ai, images, media, automation, metadata
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically generate SEO-optimized titles, descriptions, and tags for your media library images using AI (OpenAI, Claude, or Gemini).

== Description ==

AI Image Tagger leverages the power of artificial intelligence to automatically generate high-quality metadata for your WordPress media library images. Save hours of manual work and improve your site's SEO with AI-generated titles, descriptions, and tags.

= Key Features =

* **Multiple AI Providers**: Choose between OpenAI GPT-4o, Anthropic Claude 3.5, or Google Gemini 1.5
* **Automatic Processing**: Automatically tag images as they're uploaded
* **Batch Processing**: Process multiple existing images at once
* **Queue System**: Background processing with retry logic for reliability
* **Cost Tracking**: Monitor your AI API usage and costs
* **REST API**: Programmatically process images via REST endpoints
* **Dashboard**: View processing statistics and success rates
* **Export/Import**: Export metadata to CSV or JSON format
* **Search Enhancement**: AI metadata included in WordPress search
* **Secure**: API keys encrypted with AES-256-CBC

= How It Works =

1. Install and activate the plugin
2. Add your OpenAI, Claude, or Gemini API key in Settings → AI Image Tagger
3. Upload images or process existing ones
4. AI automatically generates optimized titles, descriptions, and tags
5. Metadata is saved to your WordPress media library

= External Services =

**IMPORTANT**: This plugin connects to external AI services to analyze your images. You must have an active API key from at least one of these providers:

* **OpenAI** - https://openai.com/
  * Service: Sends images to OpenAI's API for analysis
  * Privacy Policy: https://openai.com/policies/privacy-policy
  * Terms of Service: https://openai.com/policies/terms-of-use
  * Pricing: https://openai.com/pricing

* **Anthropic (Claude)** - https://www.anthropic.com/
  * Service: Sends images to Anthropic's API for analysis
  * Privacy Policy: https://www.anthropic.com/legal/privacy
  * Terms of Service: https://www.anthropic.com/legal/terms
  * Commercial Terms: https://www.anthropic.com/legal/commercial-terms

* **Google (Gemini)** - https://ai.google.dev/
  * Service: Sends images to Google's Gemini API for analysis
  * Privacy Policy: https://policies.google.com/privacy
  * Terms of Service: https://policies.google.com/terms
  * API Terms: https://ai.google.dev/gemini-api/terms

**Data Transmission**: When you process an image, the plugin sends the image file (base64 encoded) to your selected AI provider's API. The AI analyzes the image and returns metadata (title, description, tags). No data is stored on external servers permanently - only transmitted for analysis.

**User Consent**: By configuring an API key and enabling automatic processing, you consent to sending your images to the selected AI provider for analysis.

= Use Cases =

* **E-commerce**: Automatically generate product image descriptions
* **Photography**: Tag portfolio images efficiently
* **Blogs**: Add SEO-optimized alt text to articles
* **News Sites**: Quick metadata for breaking news images
* **Marketing**: Streamline social media image preparation

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* API key from OpenAI, Anthropic, or Google
* Active internet connection
* WordPress REST API enabled

== Installation ==

= From WordPress Dashboard =

1. Navigate to Plugins → Add New
2. Search for "AI Image Tagger"
3. Click "Install Now" then "Activate"
4. Go to Settings → AI Image Tagger
5. Enter your API key for OpenAI, Claude, or Gemini
6. Configure your preferences
7. Start processing images!

= Manual Installation =

1. Download the plugin ZIP file
2. Navigate to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin
5. Configure your API keys in Settings → AI Image Tagger

= Initial Configuration =

1. Go to Settings → AI Image Tagger
2. Select your preferred AI provider (OpenAI, Claude, or Gemini)
3. Enter your API key (get one from the provider's website)
4. Click "Test Connection" to verify it works
5. Configure automatic processing options
6. Set your batch size and processing limits
7. Save settings

== Frequently Asked Questions ==

= Do I need an API key? =

Yes, you need an API key from at least one of the supported AI providers: OpenAI, Anthropic (Claude), or Google (Gemini). Free trial credits are often available.

= How much does it cost? =

The plugin itself is free. However, you'll pay for API usage from your chosen provider:
* OpenAI GPT-4o: ~$0.003 per image
* Anthropic Claude: ~$0.004 per image
* Google Gemini: ~$0.001 per image

Exact costs depend on image size and complexity.

= Is my data secure? =

Yes. API keys are encrypted with AES-256-CBC encryption using WordPress salts. Images are only transmitted to the AI provider for analysis and are not stored externally.

= Can I process existing images? =

Yes! Use the bulk action in the Media Library to process multiple images at once, or click "Generate metadata" on individual images.

= What happens if processing fails? =

The plugin includes automatic retry logic. Failed items are retried up to 3 times. You can view failed items in the dashboard and manually retry them.

= Can I edit AI-generated metadata? =

Absolutely! All generated metadata can be edited manually in the Media Library like any other WordPress metadata.

= Does this work with other languages? =

Yes, the plugin is translation-ready and AI can generate metadata in multiple languages based on your prompt configuration.

= Can I use this with WooCommerce? =

Yes! The plugin works with any WordPress media library images, including WooCommerce product images.

= Is there a rate limit? =

Rate limits are set by your AI provider. The plugin includes configurable batch processing to help manage API rate limits.

= Can I customize the AI prompts? =

Yes, you can set custom prompts in the plugin settings to guide the AI's metadata generation.

== Screenshots ==

1. Settings page - Configure your AI provider and API keys
2. Dashboard - View processing statistics and costs
3. Media Library - AI status column and bulk actions
4. Media Edit - Generate or regenerate metadata for individual images
5. Metabox - View AI-generated metadata on attachment edit screen

== Changelog ==

= 1.0.0 =
* Initial release
* Support for OpenAI GPT-4o
* Support for Anthropic Claude 3.5 Sonnet
* Support for Google Gemini 1.5 Pro
* Automatic image processing on upload
* Manual processing for existing images
* Batch processing via queue system
* Cost tracking and analytics
* REST API endpoints
* Export/Import functionality
* Dashboard with statistics
* WordPress search integration
* Multi-language support foundation

== Upgrade Notice ==

= 1.0.0 =
Initial release of AI Image Tagger.

== Privacy Policy ==

AI Image Tagger connects to external AI services to analyze your images. Please review the privacy policies of your chosen provider:

* OpenAI Privacy Policy: https://openai.com/policies/privacy-policy
* Anthropic Privacy Policy: https://www.anthropic.com/legal/privacy
* Google Privacy Policy: https://policies.google.com/privacy

The plugin does not collect or store any user data beyond what is necessary for WordPress functionality. API keys are stored encrypted in your WordPress database.

== Support ==

For support, please visit: https://codevera.ai/support/

== Credits ==

Developed by Codevera (https://codevera.ai)

== License ==

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
