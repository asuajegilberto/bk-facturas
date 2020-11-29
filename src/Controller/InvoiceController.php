<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route("/invoice")
 */
class InvoiceController extends AbstractController
{

    private $invoiceRepository;
    private $usersRepository;
    private $mailer;

    public function __construct(InvoiceRepository $invoiceRepository,UsersRepository $usersRepository,\Swift_Mailer $mailer)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->usersRepository = $usersRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="invoice_index", methods={"POST"})
     */
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $invoice = $this->invoiceRepository->findBy(['Users'=>1]);
        return $this->json($invoice);
    }

    /**
     * @Route("/new", name="invoice_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        
        
        if(empty($data['description']) || empty($data['productService']) || empty($data['subTotal']) || empty($data['discount']) || empty($data['total']) || empty($data['email']) || empty($data['user']) ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }

          $user = $this->usersRepository->findOneBy(['id'=>$data['user']]);
          $token = sha1(mt_rand(1, 90000) . 'SALT');
          
          $this->invoiceRepository->save($data['description'],  $data['productService'] ,$data['subTotal'] ,$data['discount'] ,$data['total'] ,$data['email'], $token,$user);
          $this->sendMail($data['email'],$token);

          return new JsonResponse(['status' => 'Invoice created!'], Response::HTTP_CREATED);
      
    }

    /**
     * @Route("/show", name="invoice_show", methods={"POST"})
     */
    public function show(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        $invoice = $this->invoiceRepository->findOneBy(['token'=>$data['token']]);

        if(empty($data['token'])  ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }
          return $this->json($invoice);
    }

    /**
     * @Route("/pay", name="invoice_pay", methods={"POST"})
     */
    public function pay(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        
        if(empty($data['id']) || empty($data['total'])  ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }

        $user = $this->usersRepository->findOneBy(['id'=>$data['id']]);

        $this->usersRepository->pay($user,$data['total']);

        return new JsonResponse(['status' => 'user updated'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/{id}/edit", name="invoice_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Invoice $invoice): Response
    {
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('invoice_index');
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="invoice_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Invoice $invoice): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('invoice_index');
    }


    public function sendMail($mail,$token)
    {
        
        $message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo($mail)
        ->setBody(
            $token

        )
    ;

    return $this->mailer->send($message);
    }
}
