<?php

// $shellCurrentUser = execOrFail("whoami");
// $shellCurrentUser = $shellCurrentUser[0]; //dirty change
// die($shellCurrentUser[0]);
//Interactive mode
if($climate->arguments->defined('interactive')) {
    $args = interactive();
    $climate->backgroundLightGreen()->black()->out('WebApp configuration :');
    $climate->backgroundLightGreen()->black()->br()->out('Web server : '.$args['web-server']);
    $climate->backgroundLightGreen()->black()->br()->out('Site Folder : /home/'.$args['user-home'].'/www/'.$args['site-name']);
    $climate->backgroundLightGreen()->black()->br()->out('Vhost file name : '.$args['vhost-name']);
    $climate->backgroundLightGreen()->black()->br()->out('Local address : '.$args['local-address']);
    $climate->backgroundLightGreen()->black()->br()->out('DocumentRoot : '.$args['document-root']);
    $input = $climate->input('Should we create this ? [Y/n]');
    $input->defaultTo('Y');
    $input->accept(['Y', 'n']);
    $response = $input->prompt();

    if($response === "Y") {
        makeApp($args);
    }
}
/**
 * Allow for interactive needed argument filling
 * @return [type] [description]
 */
function interactive() : array
{
    global $climate;
    $args = [];

    $input = $climate->input('Web server ? default : apache2');
    $input->defaultTo('apache2');
    $args['web-server'] = $input->prompt();

    $input = $climate->input('User home folder ?');
    $input->accept(function($response) {
        return ($response !== '');
    });
    $args['user-home'] = $input->prompt();

    $input = $climate->input('Site name folder ? ex : site-name');
    $input->accept(function($response) {
        return ($response !== '');
    });
    $args['site-name'] = $input->prompt();

    $input = $climate->input('Vhost file name ? ex : 001-site-name.conf');
    $input->accept(function($response) {
        return ($response !== '');
    });
    $args['vhost-name'] = $input->prompt();

    $input = $climate->input('Local address ? default : site-name.work');
    $input->defaultTo($args['site-name'].".work");
    $args['local-address'] = $input->prompt();

    $input = $climate->input('Document Root ? default : /var/www/site-name/public');
    $input->defaultTo("/var/www/".$args['site-name']."/public");
    $args['document-root'] = $input->prompt();

    return $args;
}

function makeApp($args) : bool
{
    global $climate;
    foreach ($args as $key => $value) {
        switch($key) {
            // case 'web-server':

            // break;
            case 'site-name':
                if($args['web-server'] === "apache2") {
                    if($climate->arguments->defined('verbose')) {
                        $climate->backgroundLightBlue()->black()->br()->out('Creating /home/'.$args['user-home'].'/www/'.$args['site-name'].' folder');
                    }
                    execOrFail('mkdir -p  /home/'.$args['user-home'].'/www/'.$args['site-name']);
                }
            break;
            case 'vhost-name':
                if($args['web-server'] === "apache2") {
                    if($climate->arguments->defined('verbose')) {
                        $climate->backgroundLightBlue()->black()->br()->out('Creating /etc/apache2/sites-available/'.$args['vhost-name'].' vhost configuration file');
                    }
                    execOrFail('touch /etc/apache2/sites-available/'.$args['vhost-name']);
                    $vhostContent = "VHOST
# /etc/apache2/sites-available/websitename.conf
<VirtualHost *:80>
ServerAdmin thomas.chartron@gmail.com

# Domaines gérés par ce virtualhost
ServerName uwithi.work
ServerAlias *.uwithi.work

# Racine Web
DocumentRoot /var/www/uwithi/public

# Règles spécifiques s&apos;appliquant à ce dossier
<Directory /var/www/uwithi/public>
Options +Indexes +FollowSymLinks
AllowOverride All
</Directory>
# Où placer les logs pour cette hôte
ErrorLog /home/thomas/dev/logs/www/uwithi-error.log
CustomLog /home/thomas/dev/logs/www/uwithi-access.log combined
</VirtualHost>
VHOST";
                    if($climate->arguments->defined('verbose')) {
                        $climate->backgroundLightBlue()->black()->br()->out('Filling vhost configuration file');
                    }
                    execOrFail('cat > /etc/apache2/sites-available/'.$args['vhost-name'].' << '.trim($vhostContent));
                }
            break;
            case 'local-address':
                if($args['web-server'] === "apache2") {
                    //edit etc/host
                }
            break;

        }
    }
    //enable site
     // a2ensite site-name
     // Reload apache2
     // Go to site-name.work
     // Auto deploy on pi webserver option
    return false;
}
