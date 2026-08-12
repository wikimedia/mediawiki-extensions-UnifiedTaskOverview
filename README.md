# UnifiedTaskOverview

## Installation
Execute

    composer require mediawiki/unified-task-overview ~1
within MediaWiki root or add `hallowelt/unifiedtaskoverview` to the
`composer.json` file of your project

## Activation
Add

    wfLoadExtension( 'UnifiedTaskOverview' );
to your `LocalSettings.php` or the appropriate `settings.d/` file.

## The `<mytasks />` tag
Lists all tasks the current user is assigned to - workflow activities, simple tasks,
read confirmations and any other type registered in `TaskDescriptorRegistry` - from all
namespaces and, in a wiki farm, from all instances.

    <mytasks />

The tag has no attributes, it is inserted as is. The list shows the type of the task, the
task itself, its description and the wiki it originates from, the latter marked with the
color of that instance. Sorting and filtering are available per column.
