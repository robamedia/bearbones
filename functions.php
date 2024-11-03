<?php
// Define the theme slug and GitHub repository details
define('THEME_SLUG', 'bearbones_theme');
define('GITHUB_USER', 'robamedia'); // Replace 'yourusername' with your GitHub username
define('GITHUB_REPO', 'bearbones'); // Replace 'yourrepository' with your theme's repository name

// Set up theme support features
function bearbones_theme_setup() {
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('block-template-parts');

    // Load default block styles.
    add_theme_support('core-block-patterns');
}
add_action('after_setup_theme', 'bearbones_theme_setup');

// Enqueue theme stylesheet
function bearbones_theme_styles() {
    wp_enqueue_style('bearbones-theme-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'bearbones_theme_styles');

// GitHub update check
function custom_github_update_check( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // Get the current theme version
    $theme_version = wp_get_theme( THEME_SLUG )->get( 'Version' );

    // GitHub API URL for the latest release
    $remote_url = "https://api.github.com/repos/" . GITHUB_USER . "/" . GITHUB_REPO . "/releases/latest";

    // Make the request to GitHub
    $response = wp_remote_get( $remote_url );

    if ( is_wp_error( $response ) ) {
        return $transient;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );

    // Check if a new version is available
    if ( isset( $data->tag_name ) && version_compare( $theme_version, $data->tag_name, '<' ) ) {
        $transient->response[ THEME_SLUG ] = [
            'theme'       => THEME_SLUG,
            'new_version' => $data->tag_name,
            'url'         => $data->html_url,
            'package'     => $data->zipball_url,
        ];
    }

    return $transient;
}
add_filter( 'site_transient_update_themes', 'custom_github_update_check' );
