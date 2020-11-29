<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;



class UsersController extends AbstractController
{
    private $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }



    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        
        if(empty($data['email']) || empty($data['password']) || empty($data['phone']) || empty($data['fullName']) ){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
          }

          $address = empty($data['address']) ? $address = '' : $address = $data['address'];
          $roles = Users::ROLE_USER;
          

          $this->usersRepository->save($data["email"],$roles,$data["password"],$data["fullName"],$address,$data["phone"]);

          return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
      
    }


}
