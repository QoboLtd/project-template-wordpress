<?php
require_once 'vendor/qobo/phake-builder/Phakefile';

/**
 * Run WP CLI batch file
 * 
 * There might be a variety of WP CLI batches that
 * is ncessary for the app (install, update, content, etc),
 * so we standartize the location and naming convention.
 * 
 * Each batch file is processed as a template befor execution
 * to make passing variables into there easier.
 * 
 * @param string $name Name of batch file (e.g.: install)
 * @param array $app App data
 * @return void
 */
function runWPCLIBatch($name, $app) {

	$src = 'etc/wp-cli.' . $name;
	$dst = $src . '.sh';
	
	$template = new \PhakeBuilder\Template($src);
	$placeholders = $template->getPlaceholders();
	$data = array();
	foreach ($placeholders as $placeholder) {
		$data[$placeholder] = getValue($placeholder, $app);
		
		// We really need wp-cli for this
		if ($placeholder == 'SYSTEM_COMMAND_WPCLI') {
			$data['SYSTEM_COMMAND_WPCLI'] = getValue($placeholder, $app) ?: './vendor/bin/wp --allow-root';
		}
	}
	$bytes = $template->parseToFile($dst, $data);
	if (!$bytes) {
		throw new \RuntimeException("Failed to create batch file");
	}

	$parts = array();
	$parts[] = getValue('SYSTEM_COMMAND_SHELL', $app) ?: '/bin/sh';
	$parts[] = $dst;
	doShellCommand($parts);
	unlink($dst);
	
}

group('app', function() {

	desc('Install application');
	task('install', ':builder:init', function($app) {
		printSeparator();
		printInfo("Installing application");
	});
	task('install', ':git:pull', ':git:checkout');
	task('install', ':composer:install');
	task('install', ':dotenv:create', ':dotenv:reload', ':file:process');
	task('install', ':mysql:database-create');
	
	// if you are downloading an archive
	// task('install', ':wordpress:prepare');
	
	// From here on, you can either import the full MySQL dump with find-replace...
	//task('install', ':mysql:database-import');
	//task('install', ':mysql:find-replace');
	// ... or have a fresh and clean install
	task('install', ':wordpress:install');


	desc('Update application');
	task('update', ':builder:init', function($app) {
		printSeparator();
		printInfo("Updating application");
	});
	task('update', ':git:pull', ':git:checkout');
	task('update', ':composer:install');
	task('update', ':dotenv:create', ':dotenv:reload', ':file:process');

	// if you are downloading an archive
	// task('update', ':wordpress:prepare');
	
	task('update', ':wordpress:update');
	


	desc('Remove application');
	task('remove', ':builder:init', function($app) {
		printSeparator();
		printInfo("Removing application");
	});
	task('remove', ':mysql:database-drop');
	task('remove', ':dotenv:delete');

});

group('wordpress', function() {

	desc("Install WordPress");
	task('install', ':builder:init', function($app) {
		printSeparator();
		printInfo("Installing WordPress");
		
		runWPCLIBatch('install', $app);
	});
	
	desc("Update WordPress");
	task('update', ':builder:init', function($app) {
		printSeparator();
		printInfo("Update WordPress");
		
		runWPCLIBatch('update', $app);
	});
	
	desc("Prepare WordPress files");
 	task('prepare', ':builder:init', function($app) {
        printSeparator();
        printInfo("Preparing WordPress");
		printSeparator();
		
		printInfo("Downloading the archive");
		//The file url
		$dlsrc = 'https://qobo.s3.amazonaws.com/shared/public/myestates.co/wp-content.zip' ;
		//The destination path
		$dldst = 'etc/wp-content.zip';
		$result = \PhakeBuilder\FileSystem::downloadFile($dlsrc, $dldst);
		if (!$result) {
			throw new \RuntimeException("Failed to download file");
		}
		printSuccess("Download complete!");
		printSeparator();
		
		//
		// The folder you want to download is first being removed
		//
		printInfo("Deleting existing folder"); 
		// Folder path to delete
		$wppath = "wp-content/";
		$result = \PhakeBuilder\FileSystem::removePath($wppath);
		if (!$result) {
			throw new \RuntimeException("Failed to remove path");
		}
		printSuccess("Delete complete!");
		printSeparator();
		
		printInfo("Extracting downloaded archive");
		// The path to the archive
		$exsrc = 'etc/wp-content.zip';
		// The path to extract the archive
		$exdst = './';
		try {
			\Phakebuilder\Archive::extract($exsrc, $exdst);
		} catch (\Exception $e) {
			throw new \RuntimeException($e->getMessage());
		}
		printSuccess("Extract complete!");
		printSeparator();
		
		//
		// The archive you specified in $exsrc will be deleted
		//
		printInfo("Deleting downloaded archive"); 
		$result = \PhakeBuilder\FileSystem::removePath($exsrc);
		if (!$result) {
			throw new \RuntimeException("Failed to remove path");
		}
		printSuccess("Delete complete!");
		printSeparator();
		
		//
		// The folder you specified in $wppath will change permissions 
		//
		printInfo("Fixing extracted folder permissions"); 
		$result = \PhakeBuilder\FileSystem::chmodPath($wppath);
		if (!$result) {
			throw new \RuntimeException("Failed to change permissions");
		}
		printSuccess("Permission change complete!");
		printSuccess("SUCCESS!");
		
 	});
	
});

# vi:ft=php
?>
