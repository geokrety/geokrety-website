# Mail templates

Crafting mails that works on most mail reader is a tedious task. We use
Foundation for Emails framework to help in this process.

https://github.com/foundation/foundation-emails

# Prepare your Installation

Check official doc at https://github.com/foundation/foundation-emails#getting-started

1. Install `nodejs` on your system
1. Go to our templates directory `cd website/app-templates/foundation-emails`
1. Launch the install `npm install`

# Directory structure

The install consists in sources templates built when modifier as the dist files.

Note: Do your updates in the `src` directory, and use the `foundation-cli` to
build the templates. Never do updates directly in the `dist` folder.

Note: The mail templates are also `smarty` templates. The internal html preview
will be inlined with smarty statements.

# Build the templates

1. Go to our templates directory `cd website/app-templates/foundation-emails`
1. Build `npm run build`

You can also have live html preview of your file using `npm start`.
