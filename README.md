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

## Build

```powershell
php D:/.agents/tools/phing-packager/phing-latest.phar -f D:/.agents/tools/phing-packager/build.xml -Dconfig=D:/Dev/wt-read-in-ai/.dist/build/package.config.json "3. Package release"
```

The installable plugin ZIP is written to `.packages/`.
