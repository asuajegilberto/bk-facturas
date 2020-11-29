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


use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class InvoiceController extends AbstractController
{

    /**
     * @var TokenStorageInterface
     */
    private $invoiceRepository;
    private $usersRepository;
    private $mailer;
    private $tokenStorage;

    public function __construct(InvoiceRepository $invoiceRepository,UsersRepository $usersRepository,\Swift_Mailer $mailer,TokenStorageInterface $tokenStorage)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->usersRepository = $usersRepository;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
    }


    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $id = $this->tokenStorage->getToken()->getUser()->getId();
        $invoice = $this->invoiceRepository->findBy(['Users'=>$id]);
        return $this->json($invoice);
    }


    public function new(Request $request): Response
    {
        $id = $this->tokenStorage->getToken()->getUser()->getId();
        $data = json_decode($request->getContent(),true);
        
        
        if(empty($data['description']) || empty($data['productService']) || empty($data['subTotal']) || empty($data['discount']) || empty($data['total']) || empty($data['email']) ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }

          $user = $this->usersRepository->findOneBy(['id'=>$id]);
          $token = sha1(mt_rand(1, 90000) . 'SALT');
          
          $this->invoiceRepository->save($data['description'],  $data['productService'] ,$data['subTotal'] ,$data['discount'] ,$data['total'] ,$data['email'], $token,$user);
          $this->sendMail($data['email'],$token);

          return new JsonResponse(['status' => 'Invoice created!'], Response::HTTP_CREATED);
      
    }


    public function show(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        $invoice = $this->invoiceRepository->findOneBy(['token'=>$data['token']]);

        if(empty($data['token'])  ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }
          return $this->json($invoice);
    }


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
