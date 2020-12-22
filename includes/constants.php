<?php
/**
 * Constant definitions
 *
 * @package PaddlePress
 */

namespace PaddlePress\Constants;

const SETTING_OPTION     = 'paddlepress_settings'; // plugin settings
const MAPPING_OPTION     = 'paddlepress_mappings'; // paddle product_id => membership post_id mappings
const LICENSE_KEY_OPTION = 'paddlepress_license_key'; // plugin license key
const DB_VERSION_OPTION  = 'paddlepress_db_version'; // paddlepress db version

const ALERT_META_NAME                  = 'paddlepress_email_alert_name'; // alert name when the email send
const MEMBERSHIP_TYPE_META_KEY         = 'paddlepress_membership_type'; // holds membership type recurring/one-off
const SUBSCRIPTION_META_KEY            = 'paddlepress_paddle_subscriptions'; // holds assigned paddle subscriptions
const SUBSCRIPTION_UPDATE_META_KEY     = 'paddlepress_paddle_update_allowed_subscriptions'; // holds paddle plan ids for update/downgrade
const PRODUCT_META_KEY                 = 'paddlepress_paddle_product'; // holds assigned paddle products
const DOWNLOAD_LICENSE_META_KEY        = 'paddlepress_download_license'; // flag if this membership level can download
const DOWNLOAD_LICENSE_COUNT_META_KEY  = 'paddlepress_download_license_count'; // allowed license count for this membership level
const DOWNLOAD_ATTACHMENT_URL_META_KEY = 'paddlepress_download_attachment_url'; // download url for the downloadable item
const DOWNLOAD_ATTACHMENT_ID_META_KEY  = 'paddlepress_download_attachment_id'; // attachment id for the downloadable item - we use this is for retrieve original file
const DOWNLOAD_VERSION_META_KEY        = 'paddlepress_download_version'; // version info
const CONTENT_ACCESS_META_KEY          = 'paddlepress_restricted_content'; // flag if this content restricted
const ACCESS_LEVELS_META_KEY           = 'paddlepress_allowed_memberships'; // allowed membership levels for content

// term meta for download items
const DOWNLOAD_COUNT_META_KEY     = 'paddlepress_download_count'; // download count for item
const PLUGIN_BANNER_LOW_META_KEY  = 'paddlepress_plugin_banner_low'; // plugin banner low quality
const PLUGIN_BANNER_HIGH_META_KEY = 'paddlepress_plugin_banner_high'; // plugin banner high quality
const PLUGIN_ICON_LOW_META_KEY    = 'paddlepress_plugin_icon_low'; // plugin icon low quality
const PLUGIN_ICON_HIGH_META_KEY   = 'paddlepress_plugin_icon_high'; // plugin icon high quality
const AUTHOR_NAME_META_KEY        = 'paddlepress_author_name'; // plugin/theme author name
const AUTHOR_URL_META_KEY         = 'paddlepress_author_url';

// CPTs
const MEMBERSHIP_POST_TYPE = 'ppp_membership'; // CPT membership
const DOWNLOAD_POST_TYPE   = 'ppp_download'; // CPT downloads
const EMAIL_POST_TYPE      = 'ppp_email'; // CPT emails

// Custom Taxonomy
const DOWNLOAD_TAX_NAME = 'paddlepress_download_tag';
