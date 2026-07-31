# WT Read In AI

Joomla content plugin that adds a configurable "Read with AI" block to article pages.

## Features

- Renders on the selected content event: `onContentAfterTitle`, `onContentBeforeDisplay`, or `onContentAfterDisplay`.
- Allows category targeting with separate "show in categories" and "hide in categories" settings.
- Uses selectable layouts from the plugin `tmpl` directory.
- Provides configurable AI service links through a repeatable plugin subform.
- Includes default ChatGPT and Claude service rows.
- Adds a prompt copy button and outbound AI links containing the current article URL.
- Includes a WebTolk installer script and plugin info field.

## Packaging

The repository root is the Joomla plugin root. To create an installable package, zip the repository contents without Git metadata or local development folders.
