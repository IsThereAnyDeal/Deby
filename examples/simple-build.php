<?php

use Ds\Set;
use IsThereAnyDeal\Tools\Deby\Runtime\Setup;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\CheckPaths;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\Copy;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\DeleteDir;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\ExecTask;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\MakeDir;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\MakeTar;
use IsThereAnyDeal\Tools\Deby\Tasks\Local\SetupRelease;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Cleanup;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Prepare;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Push;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Ready;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Release;
use IsThereAnyDeal\Tools\Deby\Tasks\Remote\Rollback;
use IsThereAnyDeal\Tools\Deby\Types\FileSet;

/*
 * In this example, the build is set up in a way that our source files will be copies to staging directory (STAGING_DIR),
 * where they will be archived. Archive will be stored in distribution directory (DIST_DIR).
 * From there, the archive will be taken and uploaded to our target.
 *
 * Following consts are for our convenience.
 */
const PROJECT_DIR = __DIR__."/..";
const DIST_DIR = PROJECT_DIR."/dist";
const STAGING_DIR = DIST_DIR."/staging";

/*
 * Deby expects config file to return function, which accepts Setup object,
 * and optionally Set<string> of (command line) options
 */
return function(Setup $setup): void {
    /*
     * Deby supports json and yaml targets config, in this example we load yaml file
     * Target config defines our targets, which consist of hosts
     */
    $setup->readTargetsConfig(__DIR__."/targets.yaml");

    /*
     * Test recipe, which runs PHPUnit tests
     */
    $setup->recipe("test")
        ->add("PHPUnit", new ExecTask("vendor\bin\phpunit tests", printOutput: false, printOnError: true));

    /*
     * Build recipe
     * 1. delete the distribution dir, if it exists
     * 2. make sure the staging dir exists
     * 3. Copy entire src/ folder to staging dir
     * 4. Create archive
     */
    $setup->recipe("build")
        ->add("Delete dist dir", new DeleteDir(DIST_DIR))
        ->add("Make staging dir", new MakeDir(STAGING_DIR))
        ->add("Stage files", new Copy(STAGING_DIR, (new FileSet(PROJECT_DIR))
            ->include("src")))
        ->add("Create archive",
            new MakeTar("dist", STAGING_DIR, DIST_DIR)
        );

    /*
     * Push recipe
     * 1. check whether there is an archive in distribution dir
     * 2. Cleanup old releases - keep only 3 releases in total
     * 3. Prepare required deby folders if they don't exist. In this example we are not creating any custom shared directories
     * 4. Setup release. This will generate a release name. You can supply your own if you want to.
     * 5. Push release. Our archive will be uploaded to the proper release folder, unpacked and release will be marked as New.
     *    If we need to, we can now do any additional work in the release folder, e.g. installing dependencies via composer
     * 6. Mark release as ready
     */
    $setup->recipe("push")
        ->add("Check build exists", new CheckPaths(DIST_DIR."/dist.tar.gz"))
        ->add("Cleanup", new Cleanup(3))
        ->add("Create folders", new Prepare([]))
        ->add("Setup release", new SetupRelease())
        ->add("Push release", new Push(DIST_DIR."/dist.tar.gz"))
        ->add("Ready release", new Ready());

    /*
     * Release recipe
     * Take the latest Ready recipe, that is newer than the current one, and make it current.
     * In essence, this will set a symlink for current/ folder to the latest releases/{release} folder
     */
    $setup->recipe("release")
        ->add("Release", new Release());

    /*
     * Deploy recipe
     * This is a special kind of recipe that does not have its own tasks, but it sets dependencies on other recipes.
     * It will run recipes test, build, push and release in order.
     */
    $setup->recipe("deploy")
        ->after("test")
        ->after("build")
        ->after("push")
        ->after("release");

    /*
     * Rollback recipe
     * Opposite of release. Sets the latest, but older than current release as the current one
     */
    $setup->recipe("rollback")
        ->add("Rollback", new Rollback());
};
