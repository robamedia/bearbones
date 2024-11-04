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

    // GitHub API URL for the latest releafunction custom_github_update_check( $transient ) {
    if ( empty( $transient->checked ) ) {
        error_log('No themes found to check.');
        return $transient;
    }

    // Log that we're in the update check function
    error_log('Running custom GitHub update check...');

    // Get the current theme version
    $theme_version = wp_get_theme( THEME_SLUG )->get( 'Version' );
    error_log('Current theme version: ' . $theme_version);

    // GitHub API URL for the latest release
    $remote_url = "https://api.github.com/repos/" . GITHUB_USER . "/" . GITHUB_REPO . "/releases/latest";
    //$response = wp_remote_get( $remote_url );

    // Add authentication headers with the GitHub personal access token
    $response = wp_remote_get( $remote_url, [
        'headers' => [
            'Authorization' => 'token ' . GITHUB_TOKEN
        ]
    ]);


    // Check for errors in the API request
    if ( is_wp_error( $response ) ) {
        error_log('GitHub API error: ' . $response->get_error_message());
        return $transient;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );
    error_log('GitHub API response: ' . print_r( $data, true ));

    // Check if a new version is available
    if ( isset( $data->tag_name ) && version_compare( $theme_version, $data->tag_name, '<' ) ) {
        error_log('New version available: ' . $data->tag_name);
        $transient->response[ THEME_SLUG ] = [
            'theme'       => THEME_SLUG,
            'new_version' => $data->tag_name,
            'url'         => $data->html_url,
            'package'     => $data->zipball_url,
        ];
    } else {
        error_log('No new version available or version check failed.');
    }

    return $transient;
}
add_filter( 'site_transient_update_themes', 'custom_github_update_check' );

function display_current_year() {
    return date('Y');
}
add_shortcode('current_year', 'display_current_year');
