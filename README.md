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
    <mytasks types="workflow,task" />

The optional `types` attribute restricts the list to the given task types (`workflow`,
`task`, `readconfirmation`); left empty, all types are shown. The inspector offers the
same choice through a multiselect field.

The list shows, in this order, the wiki a task originates from (marked with the color of
that instance), its namespace, the page it belongs to, its description and its type.
Sorting and filtering are available per column; the namespace column is hidden by default
and can be switched back on through the column menu.
