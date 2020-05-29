<?php


namespace App\Controller\Admin;


use App\Entity\Stock;
use App\Form\StockType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;


class AdminStockController extends AdminBaseController
{
    /**
     * @Route("/admin/stocks", name="admin_stocks")
     * @return Response
     */

    public function index()
    {
        $stocks = $this->getDoctrine()->getRepository(Stock::class)->findAll();

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Акции';
        $forRender['h1'] = 'Список всех акций';
        $forRender['stocks'] = $stocks;
        return $this->render("admin/stock/index.html.twig", $forRender);
    }

    /**
     * @Route("/admin/stock/create", name="admin_stock_create")
     * @param Request $request
     * @return RedirectResponse|Response
     *
     */

    public function create(Request $request)
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);


        if (($form->isSubmitted()) && ($form->isValid()))
        {

            $now = new \DateTime('now');
            $stock->setCreateAt($now->modify('+7 hour'));
//
//            $uploadedFile = $request->files->get('image')['fileUploader'];
//            $fileName = $fileUploader->upload($uploadedFile, $this->getParameter('gallery_upload'));
//            $image->setImage($fileName);

//            /** @var UploadedFile $image */
//            $image = $form->get('image')->getData();
//
//            // this condition is needed because the 'brochure' field is not required
//            // so the PDF file must be processed only when a file is uploaded
//                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
//                // this is needed to safely include the file name as part of the URL
////                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
////                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
//
//                $newFilename = $originalFilename . '-' . uniqid() . '.' . $image->guessExtension();
//                // Move the file to the directory where brochures are stored
//                try {
//                    $image->move(
//                        $this->getParameter('brochures_directory'),
//                        $newFilename
//                    );
//                } catch (FileException $e) {
//                    // ... handle exception if something happens during file upload
//                }
//
//                // updates the 'brochureFilename' property to store the PDF file name
//                // instead of its contents
//                $stock->setImage($newFilename);

//            $file = $stock->getImage();
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // перемещает файл в каталог, где хранятся брошюры
            $file->move(
                $this->getParameter('brochures_directory'),
                $fileName
            );

            // обновляет свойство 'brochure', чтобы сохранить имя файла PDF
            // вместо его содержаиия
            $stock->setImage($fileName);


            $em->persist($stock);
            $em->flush();

            return $this->redirectToRoute("admin_stocks");
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание новой акции';
        $forRender['form'] = $form->createView();
        return $this->render("admin/stock/form.html.twig", $forRender);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() уменьшает схожесть имён файлов, сгенерированных
        // uniqid(), которые основанный на временных отметках
        return md5(uniqid());
    }

    /**
     * @Route("/admin/stock/update", name="admin_stock_update")
     * @param Request $request
     * @return RedirectResponse|Response
     *
     */

    public function update(Request $request)
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);


        if (($form->isSubmitted()) && ($form->isValid()))
        {

            $stock->setCreateAt(new \DateTime('today'));
//            $form->get('update_at')->setData(new \DateTime('today'));
//            $stock->setUpdateAt(new \DateTime('today'));

            $em->persist($stock);
            $em->flush();

            return $this->redirectToRoute("admin_stocks");
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание новой акции';
        $forRender['form'] = $form->createView();
        return $this->render("admin/stock/form.html.twig", $forRender);
    }
}