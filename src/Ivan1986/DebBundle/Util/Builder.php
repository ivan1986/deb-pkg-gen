<?php

namespace Ivan1986\DebBundle\Util;

use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\SimplePackage;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * Сборщик пакетов
 */
class Builder
{
    /** @var string Путь до пакетов */
    private $path;

    /** @var TwigEngine Шаблонизатор */
    private $t;

    public function __construct(\Symfony\Bundle\TwigBundle\TwigEngine $templating)
    {
        $this->path = dirname(__DIR__).'/package';
        $this->t = $templating;
    }

    /**
     * Собирает простой пакет из репозитория
     *
     * @param Repository $repo
     * @return bool|SimplePackage
     */
    public function simplePackage(Repository $repo)
    {
        $data = $this->build($repo);
        if (!$data)
            return false;
        $package = new SimplePackage();
        $package->setContent($data['content']);
        $package->setFile($data['file']);
        $package->setInfo($data['finfo']);
        $package->setRepository($repo);
        return $package;
    }

    /**
     * Собирает произвольный пакет из репозитория
     *
     * @param Repository $repo Репозиторий
     * @return array|bool данные, которые нужно записать в класс пакета
     */
    public function build(Repository $repo)
    {
        $dir = $this->path.'/'.$repo->pkgName();

        $lockf = $dir.'.lock';
        $lockr = fopen($lockf, 'w');
        if (!$lockr)
            return false;
        $lock = flock($lockr, LOCK_EX | LOCK_NB);
        if (!$lock)
            return false;
        //Копирование и изменение шаблона
        $fs = new Filesystem();
        $fs->mirror($this->path.'/tmpl', $dir);
        $files = array('control', 'changelog', 'install');
        foreach($files as $file)
        {
            file_put_contents($dir.'/debian/'.$file, $this->t->render('Ivan1986DebBundle:Builder:'.$file.'.txt.twig', array(
                'repo' => $repo,
            )));
        }
        file_put_contents($dir.'/'.$repo->pkgName().'.list', $repo->getDebStrings());
        file_put_contents($dir.'/'.$repo->pkgName().'.gpg', $repo->getKey()->getData());

        $env = array(
            'PATH' => '/bin:/usr/bin',
            'HOME' => $dir,
        );
        //Сборка пакета
        $p = new Process('dpkg-buildpackage -b -uc -tc');
        $p->setEnv($env);
        $p->setWorkingDirectory($dir);
        $p->run();
        $exit = $p->getExitCode();
        if ($exit)
            return false;

        //файл changes
        $fname = basename(glob($dir.'*.changes')[0]);
        $finfo = file($this->path.'/'.$fname);

        $parse = array();
        foreach($finfo as $k=>$line)
        {
            $line = trim($line);
            if (!isset($finfo[$k+1]))
                break;
            $line2 = trim($finfo[$k+1]);
            if ($line == 'Checksums-Sha1:')
                $parse['str-sha1'] = $line2;
            if ($line == 'Checksums-Sha256:')
                $parse['str-sha256'] = $line2;
            if ($line == 'Files:')
                $parse['str-file'] = $line2;
        }
        $info = array();
        $str = explode(' ',$parse['str-sha1']);
        $info['SHA1'] = $str[0];
        $str = explode(' ',$parse['str-sha256']);
        $info['SHA256'] = $str[0];
        $str = explode(' ',$parse['str-file']);
        $info['MD5sum'] = $str[0];
        $info['Size'] = $str[1];
        $info['Filename'] = $file = $str[4];
        $content = file_get_contents($this->path.'/'.$file);

        $p = new Process('dpkg --info '.$this->path.'/'.$file);
        $p->setEnv($env);
        $p->setWorkingDirectory($dir);
        $p->run();
        $exit = $p->getExitCode();
        if ($exit)
            return false;
        $finfo = $p->getOutput();
        $finfo = str_replace("\n ", "\n", $finfo);
        $finfo = substr($finfo, strpos($finfo, 'Package:'));
        $fileinfo = '';
        $fileinfo .= "Filename: %filename%\n";
        $fileinfo .= 'Size: '.$info['Size']."\n";
        $fileinfo .= 'SHA256: '.$info['SHA256']."\n";
        $fileinfo .= 'SHA1: '.$info['SHA1']."\n";
        $fileinfo .= 'MD5sum: '.$info['MD5sum']."\n";
        $finfo = str_replace("Description:", $fileinfo."Description:", $finfo);

        /**
         * Теперь у нас есть:
         *
         * $content - содержимое пакета
         * $file - имя файла
         *
         * $finfo - блок для списка пакетов
         * в блоке прописаны суммы и есть тег %fulename%
         * для подстановки полного имени файла
         */

        unlink($this->path.'/'.$file);
        unlink($this->path.'/'.$fname);

        $fs->remove($dir);
        flock($lockr, LOCK_UN);
        fclose($lockr);
        unlink($lockf);

        return array(
            'content' => $content,
            'file' => $file,
            'finfo' => $finfo,
        );
    }

}
