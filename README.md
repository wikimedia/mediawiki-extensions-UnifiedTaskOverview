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
Lists the open tasks of one type the current user is assigned to, from all namespaces and
from all instances.

    <mytasks />
    <mytasks type="readconfirmation" />

The `type` attribute names the kind of task the list shows: `task` for the tasks of
SimpleTasks (the default), `readconfirmation` for the read confirmations of
PageReadConfirmations, `workflow` for the activities of Workflows. Every type is offered
as a droplet of its own, so editors pick the list they want when inserting it; the
inspector of the tag offers the same choice afterwards.

The list shows, in this order, the wiki a task originates from (marked with the color of
that instance), its namespace, the page it belongs to and its description. Sorting and
filtering are available per column; the namespace column is hidden by default and can be
switched back on through the column menu.
