<?php
/**
Plugin Name: SEO Bulk Admin
Plugin URI:
Description: Easily manage posts, pages, and WooCommerce products with SEO Bulk Admin. Bulk assign categories and tags, delete posts, categories, tags, and more.
Version:  1.0.0
Author: AMP Publisher
Author URI: https://ampwptools.com/
License: GPLv3
 *
 * @package AMP Publisher
 */

defined( 'ABSPATH' ) || die();
require_once __DIR__ . '/class-tacwp-postmgr-core.php';

$tacwp_postmgr = new Tacwp_Postmgr_Core( 'postmgr', __DIR__ );

$tacwp_postmgr->init();
