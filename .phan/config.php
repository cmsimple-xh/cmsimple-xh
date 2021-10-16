<?php

use \Phan\Config;

/**
 * This configuration will be read and overlayed on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 *
 * @see src/Phan/Config.php
 * See Config for all configurable options.
 *
 * A Note About Paths
 * ==================
 *
 * Files referenced from this file should be defined as
 *
 * ```
 *   Config::projectPath('relative_path/to/file')
 * ```
 *
 * where the relative path is relative to the root of the
 * project which is defined as either the working directory
 * of the phan executable or a path passed in via the CLI
 * '-d' flag.
 */
return [
    // If true, missing properties will be created when
    // they are first seen. If false, we'll report an
    // error message.
    "allow_missing_properties" => false,

    // Allow null to be cast as any type and for any
    // type to be cast to null.
    "null_casts_as_any_type" => false,

    // Backwards Compatibility Checking
    'backward_compatibility_checks' => false,

    // Run a quick version of checks that takes less
    // time
    "quick_mode" => false,

    // Only emit critical issues
    "minimum_severity" => 10,

    // A set of fully qualified class-names for which
    // a call to parent::__construct() is required
    'parent_constructor_required' => [
    ],

    // A list of directories holding code that we want
    // to parse, but not analyze
    "exclude_analysis_directory_list" => [
        '.phan/stubs'
    ],

    'directory_list' => [
        '.phan/stubs',
    ],

    'simplify_ast' => true,
];
