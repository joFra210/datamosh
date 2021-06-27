<?php


namespace App\Controller;


use App\Entity\Upload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     * @return Response
     */
    public function home(Request $request, SluggerInterface $slugger): Response
    {
        $checkit = 'Hi there, let me destroy your files:';

        $upload = new Upload();

        $form = $this->createFormBuilder($upload)
            ->add('file', FileType::class)
            ->add('save', SubmitType::class, ['label' => 'upload'])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $upload->getFile();

            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $fileEnding = strtolower($uploadedFile->getClientOriginalExtension());
            $fileNameAvi = str_replace($fileEnding, 'avi', $newFilename);

            // Move the file to the tmp directory
            try {
                $uploadedFile = $uploadedFile->move(
                    'media/tmp',
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $ogFilePath = $uploadedFile->getRealPath();
            $tmpDir = "/var/www/html/public/media/tmp/";
            $ogAviFile = $tmpDir.$fileNameAvi;
            $cmd = "ffmpeg -i $ogFilePath -q:v 1 -y $ogAviFile";

            $process = new Process([
                'ffmpeg',
                '-i',
                $ogFilePath,
                '-q:v',
                '1',
                '-y',
                $ogAviFile]);
            echo "<h1>CONVERT TO AVI FILE</h1>";
            $process->start();

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    echo "<strong>Read from stdout:</strong> <br>".nl2br($data);
                    echo "<br>";
                } else { // $process::ERR === $type
                    echo "<strong>Read from stdout:</strong> <span style='color: #aa0000'>$type</span> <br>".nl2br($data);
                    echo "<br>";
                }
            }

            $dataMoshFileName = "dm-".$fileNameAvi;
            $dataMoshAviPath = $tmpDir.$dataMoshFileName;

            $dataMosh = new Process([
                'datamosh',
                $ogAviFile,
                '-o',
                $dataMoshAviPath,
            ]);
            echo "<h1>DATAMOSH:</h1>";
            $dataMosh->start();

            foreach ($dataMosh as $type => $data) {
                if ($dataMosh::OUT === $type) {
                    echo "<strong>Read from stdout:</strong> <br>".nl2br($data);
                    echo "<br>";
                } else { // $process::ERR === $type
                    echo "<strong>Read from stdout:</strong> <span style='color: #aa0000'>$type</span> <br>".nl2br($data);
                    echo "<br>";
                }
            }
            echo "<h1>CONVERT DATAMOSH TO MP4</h1>";

            $targetDir = "/var/www/html/public/media/broken/";
            $finalPath = $targetDir.$newFilename;
            $conversionProcess = new Process([
                'ffmpeg',
                '-i',
                $dataMoshAviPath,
                $finalPath
            ]);
            $conversionProcess->start();

            foreach ($conversionProcess as $type => $data) {
                if ($conversionProcess::OUT === $type) {
                    echo "<strong>Read from stdout:</strong> <br>".nl2br($data);
                    echo "<br>";
                } else { // $process::ERR === $type
                    echo "<strong>Read from stdout:</strong> <span style='color: #aa0000'>$type</span> <br>".nl2br($data);
                    echo "<br>";
                }
            }

            echo "<h1>FINISH!</h1>";

            $conversionProcess->wait();

            return $this->redirectToRoute('app_default_mosh', ['filePath' => $finalPath]);
        }
        return $this->render(
            'default/home.html.twig', [
                'checkit' => $checkit,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route ("/mosh")
     * @return Response
     */
    public function mosh(Request $request): Response
    {
        $filePath = $request->get('filePath');
        if ($filePath) {
            $download = new File($filePath);
            $pathName = $download->getPathname();
        } else {
            $pathName = $filePath;
        }
        return new BinaryFileResponse($download->getFileInfo());
    }

}
