<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    private $passwordEncoder;
    public function __construct(ManagerRegistry $registry,UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($registry, Users::class);
        $this->passwordEncoder = $passwordEncoder;
    }

    public function save($email,$roles,$password,$fullName,$address,$phone){
        try {
            $user = new Users();
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPassword($this->passwordEncoder->encodePassword($user,$password));
            $user->setFullName($fullName);
            $user->setAddress($address);
            $user->setPhone($phone);
            $user->setBalance(0.0);
           
            $this->_em->persist($user);
            $this->_em->flush();

            return $user;
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    public function pay(Users $user,$balance){

        $user->setBalance($balance);
        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }

    // /**
    //  * @return Users[] Returns an array of Users objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
