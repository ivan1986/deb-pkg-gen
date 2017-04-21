<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * Deploy to prod
     */
    public function deploy()
    {
        $git = $this->taskGitStack()
            ->stopOnFail()
            ->pull();

        $composer = $this->taskComposerInstall()
            //->noDev()
            //->optimizeAutoloader()
        ;

        $symfony = $this->taskExecStack()
            ->stopOnFail()
            ->exec('bin/console doctrine:migrations:migrate --no-interaction --env=prod')
            ->exec('bin/console assetic:dump --env=prod')
            ->exec('bin/console cache:warmup --env=prod')
        ;

        $this->taskSshExec('ivan1986.tk')
            ->user('web')
            ->remoteDir('/srv/web/pkggen')
            ->exec($git)
            ->exec($composer)
            ->exec('rm -rf var/cache/prod')
            ->exec($symfony)
            ->run();
    }

    /**
     * Dump database from production
     */
    public function dumpProd()
    {
        $this->taskSshExec('ivan1986.tk')
            ->user('web')
            ->remoteDir('/srv/web')
            ->exec('mysqldump -uroot pkggen > pkggen.sql')
            ->run();
        $this->taskRsync()
            ->fromPath('web@ivan1986.tk:~/pkggen.sql')
            ->toPath('pkggen.sql')
            ->run();
        $this->taskSshExec('ivan1986.tk')
            ->user('web')
            ->remoteDir('/srv/web')
            ->exec('rm pkggen.sql')
            ->run();
    }
}
