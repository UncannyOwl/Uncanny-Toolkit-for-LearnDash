const fs = require('fs-extra');
const prompts = require('prompts');
const slugify = require('slugify');
const zipFolder = require('zip-folder');
const minimist  = require('minimist');

// Used as the name of the final zipped package
const pluginSlug = 'uncanny-learndash-toolkit';

// The default version number of the final zipped package
// -- ex . uncanny-learndash-toolkit-0-0-0.zip
let version = '0-0-0';

// Prompted question to set the version of the final zipped package
const questions = [{
    type: 'text',
    name: 'version',
    message: 'What is the new versions on the plugin?'
}];

// If the script is cancelled before the version is set
const onCancel = prompt => {
    console.log('A version number must be provided -- EXITING');
    return true;
};

/**
 * Step one of the zip-plugin process
 * Asks in the command line what the new version of the plugin is then
 * starts to create needed directories
 */
(async () => {

    var argv = minimist(process.argv.slice(2));
    version  = argv['v'];
    console.log( 'Version passed: ' + version );

    //injecting version automatically
    prompts.inject( [ version ] );

    // Adds questions to the CLI
    const response = await prompts(questions, {onCancel});

    // Only continue if version has been set in th CLI
    if (response.version === '') {
        console.log('A version number must be provided -- EXITING');
    } else {
        slugify.extend({'.': '-'});
        version = slugify(response.version);
        console.log('Set Version: ' + version);

        create_dir();
    }
})();

/**
 * Step two of the zip-plugin process
 * Creates the zip-package and temp directories
 * Starts adding files to the temp directory
 */
function create_dir() {

    // Create a directory where the final zip will be stored
    fs.ensureDir('./zip-package')
        .then(() => {
            console.log('Created: zip-package directory');
        })
        .catch(err => {
            console.error(err)
        });

    // Create a temp directory where the needed plugin package files will be stored temporarily
    fs.ensureDir('./temp/' + pluginSlug)
        .then(() => {
            console.log('Created: temp plugin directory');
        })
        .catch(err => {
            console.error(err)
        });

    add_files();
}

/**
 * Step three of the zip-plugin process
 * Adds all needed plugin files to the temp directory
 * Starts the zipping process
 */
function add_files() {

    // This process will copy all needed plugin files from the plugin into a temp directory that will later be zipped
    (async () => {
        try {

            await fs.copy('./' + pluginSlug + '.php', './temp/' + pluginSlug + '/' + pluginSlug + '.php');
            console.log('Copied: main plugin file');

            await fs.copy('./readme.txt', './temp/' + pluginSlug + '/readme.txt');
            console.log('Copied: readme file');

            await fs.copy('./languages/', './temp/' + pluginSlug + '/languages');
            console.log('Copied: languages directory');

            await fs.copy('./src/assets/backend/', './temp/' + pluginSlug + '/src/assets/backend');
            console.log('Copied: assets/dist/backend directory');

            await fs.copy('./src/assets/frontend/dist/', './temp/' + pluginSlug + '/src/assets/frontend/dist');
            console.log('Copied: assets/frontend/dist directory');

            await fs.copy('./src/assets/vendor', './temp/' + pluginSlug + '/src/assets/vendor');
            console.log('Copied: assets/vendor directory');

            await fs.copy('./src/blocks/dist', './temp/' + pluginSlug + '/src/blocks/dist');
            console.log('Copied: blocks/dist directory');

            await fs.copy('./src/blocks/src', './temp/' + pluginSlug + '/src/blocks/src');
            console.log('Copied: blocks/src directory');

            await fs.copy('./src/blocks/blocks.php', './temp/' + pluginSlug + '/src/blocks/blocks.php');
            console.log('Copied: blocks/dist/blocks.php file');

            await fs.copy('./src/classes', './temp/' + pluginSlug + '/src/classes');
            console.log('Copied: classes directory');

            await fs.copy('./src/includes', './temp/' + pluginSlug + '/src/includes');
            console.log('Copied: includes directory');

            await fs.copy('./src/interfaces', './temp/' + pluginSlug + '/src/interfaces');
            console.log('Copied: interfaces directory');

            await fs.copy('./src/templates', './temp/' + pluginSlug + '/src/templates');
            console.log('Copied: templates directory');

            await fs.copy('./src/admin-menu.php', './temp/' + pluginSlug + '/src/admin-menu.php');
            console.log('Copied: boot.php file');

            await fs.copy('./src/boot.php', './temp/' + pluginSlug + '/src/boot.php');
            console.log('Copied: boot.php file');

            await fs.copy('./src/config.php', './temp/' + pluginSlug + '/src/config.php');
            console.log('Copied: config.php file');

            zip_folder();

        } catch (err) {
            console.error(err);
        }
    })();
}

/**
 * Step four of the zip-plugin process
 * Zips the temp directory and names the zip file with the version number as the suffix
 */
function zip_folder() {
    zipFolder('./temp/', './zip-package/' + pluginSlug + '-' + version + '.zip', function (err) {
        if (err) {
            console.log('oh no!', err);
        } else {
            console.log('Created: ZIP package');
            fs.remove('./temp/', err => {
                if (err) {
                    return console.error(err);
                }
                console.log('Removed: temp zip file');
            });
        }
    });


}
