<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->name('*.php')
    ->in(__DIR__)
;
return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@Symfony' => true,
            '@PHP71Migration' => true,
            '@PHP71Migration:risky' => true,
            'single_blank_line_before_namespace' => true,
            'ordered_imports' => true,
            'concat_space' => ['spacing' => 'none'],
            'phpdoc_no_alias_tag' => ['type' => 'var'],
            'no_mixed_echo_print' => ['use' => 'echo'],
            'binary_operator_spaces' => ['align_double_arrow' => false, 'align_equals' => false],
            'general_phpdoc_annotation_remove' => ['author', 'category', 'copyright', 'created', 'license', 'package', 'since', 'subpackage', 'version'],
            'native_function_invocation' => true,
            'fully_qualified_strict_types' => true,
        ]
    )
    ->setFinder($finder)
    ->setUsingCache(true)
    ;