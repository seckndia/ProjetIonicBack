<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity\Compts;
use App\Entity\Depots;
use App\Entity\Envoie;
use App\Entity\Tarifs;
use App\Form\UserType;
use App\Entity\Retrait;
use App\Form\ComptType;
use App\Form\EnvoiType;
use App\Form\LoginType;
use App\Form\RetraiType;
use App\Form\BloquerType;
use App\Form\RetraitType;
use App\Entity\Partenaire;
use App\Entity\Transaction;
use App\Form\ComptuserType;
use App\Form\PartenaireType;
use App\Form\TransactionType;
use App\Repository\UserRepository;
use App\Repository\ComptsRepository;
use App\Repository\RetraitRepository;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

  /**
   * @Route("/api")
   */
class TransactionController extends AbstractController
{
  

    /**
     * @Route("/ajoutpart", name="ajoutpart", methods={"POST"}) 
     * @IsGranted("ROLE_SUPERADMIN")

     */
     

     //-------Ajout d'un Partenaire et son Admin et Compt ----/////
     public function ajoutpart(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager , ValidatorInterface $validator)
     {
         $values=$request->request->all();//si form
 
 
         $part = new Partenaire();
 
         $form=$this->createForm(PartenaireType::class, $part);
 
         $form->handleRequest($request);
        
         $form->submit($values);
 
          $part->setStatus('Activer');
           
          $compt = new Compts();
 
          // Enregistrons les informations de date dans des variables
          $form=$this->createForm(ComptType::class, $compt);
          $form->handleRequest($request);
         
          $form->submit($values);
                  $jours = date('d');
                  $mois = date('m');
                  $annee = date('Y');
          
                 $heure = date('H');
                 $minute = date('i');
                 $seconde= date('s');
      $test = $jours.$mois.$heure.$annee.$seconde.$minute;
          
          $compt->setNumcompt($test);
          //var_dump($test);die();
  
          $compt->setPartenaire($part);

          $compt->setSolde(0);
 
 
          $user = new User();
         $form = $this->createForm(UserType::class, $user);
         $form->handleRequest($request);
                   
         $form->submit($values);
                 
          $user->setPassword($passwordEncoder->encodePassword($user,
             $form->get('password')->getData())); 
 
         
              $user->setRoles(['ROLE_ADMIN']);    
                      
                       
             $user->setStatus('Activer');
             $user->setPartenaire($part);
             $user->setNumcompt($compt);
            
             $entityManager = $this->getDoctrine()->getManager();
 
             $entityManager->persist($user);
             $entityManager->persist($part);
             $entityManager->persist($compt);
             $entityManager->flush();
     
             $data = [
                 'statut' => 201,
                 'Messages' => 'Le partenaire ajouter'
             ];
 
             return new JsonResponse($data, 201);

       
 
            }


    /**
     * @Route("/ajoutpartuser", name="ajoutpartuser", methods={"POST"})
     * @IsGranted({"ROLE_SUPERADMIN", "ROLE_ADMIN"})
     
     */

     //-------Ajout des users d'un partenaire  et du superAdmin ----/////
    public function ajoutpartuser(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
    
     $values=$request->request->all();//si form
     
     $user = new User();
     $form = $this->createForm(UserType::class, $user);
     $form->handleRequest($request);
      
     $form->submit($values);
    
       if ($form->isSubmitted()) {
       
        $user->setPassword(
           $passwordEncoder->encodePassword(
               $user,
               $form->get('password')->getData()
           )
       );        
       
       if ($values['profil']==1) {
        $user->setRoles(['ROLE_SUPERADMIN']);    
    }
   
    if ($values['profil']==2) {
        $user->setRoles(['ROLE_CAISSIER']);    
    }
    if ($values['profil']==3) {
        $user->setRoles(['ROLE_ADMIN']);    
                }
                 
   if ($values['profil']==4) {
       $user->setRoles(['ROLE_USER']);    
             }

     $partenaire=$this->getUser()->getPartenaire();

            $user->setPartenaire($partenaire);
            $user->setStatus('Activer');
      

          
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($user);
            $entityManager->flush();
            $data = [
                'statu' => 201,
                'messages' => 'Lutilisateur ajouter'
            ];

            return new JsonResponse($data, 201);
   }
  // return new Response($validator->validate($form));

}

 //---------------Faire un dépots--------------------//////

    /**
     * @Route("/depot", name="depot", methods={"POST"})
     * @IsGranted("ROLE_CAISSIER")
     * 
     */
    public function depot(Request $request, EntityManagerInterface $entityManager): Response
    {

        $depot = new Depots();
     
      
         $values = $request->request->all();
       
        $depot->setDateDepot(new \DateTime());
        $repo = $this->getDoctrine()->getRepository(Compts::class);

        $compt = $repo->findOneBy(['numcompt' => $values['numcompt']]);
      //Si var_dump ne march pas  return new JsonResponse( $compt->getId());

      $compt->getId();

if($values['montant'] >= 75000 && $compt) {
$depot->setSoldeInitial($compt->getSolde());
$compt->setSolde($values['montant']+$compt->getSolde());
$depot->setMontant($values['montant']);

             $depot->setCompt($compt);

            // $depot->setMontant($values['montant']);
             $user = $this->getUser();

             $depot->setCaissier($user);

            // $depot->setSoldeInitial($compt->getSolde());


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($depot);

            $entityManager->flush();
            $data = [
                'statut' => 201,
                'messages' => 'Depot effectuer avec succees'
            ];

            return new JsonResponse($data, 201);
        } else {
            $err = [
                'statut' => 500,
                'messages' => 'veillez saisir un montant superieur ou egal a 75000'
            ];

            return new JsonResponse($err, 500);
        }
    }

//--------------------Affectation de compte a un user------------------------////

/**
 * @Route("/afectcompt/{id}" , name="afectationCompt", methods={"POST"})
 * @IsGranted("ROLE_ADMIN")
 */

public function afectcompt(Request $request, UserRepository $userRepo,EntityManagerInterface $entityManager, User $user): Response
{

    $values=$request->request->all();


$compt = new Compts();
$entityManager = $this->getDoctrine()->getManager();
$form = $this->createForm(ComptuserType::class, $compt);
        $form->handleRequest($request);
         
        $form->submit($values);

$compte = $entityManager->getRepository(Compts::class)->findOneBy([
    'numcompt'=>$values['numcompt'],
    ]); 

$part=$compte->getPartenaire();

$partAdmin=$this->getUser()->getPartenaire();

if($partAdmin != $part){
    return new Response('Le compte n appartient pas a votre entreprise',Response::HTTP_CREATED);

}

   $user->setNumCompt($compte);
   $entityManager->flush();
   $data = [
    'status' => 200,
    'message' => 'afectation réussis'
];
return new JsonResponse($data);

}
            //////------------Envoie------------///////////////

    /**
     *@Route("/envoi", name="envoi", methods={"POST"})
     *@IsGranted({"ROLE_USER", "ROLE_ADMIN"})
     
     */

    public function envoie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $values = $request->request->all();

      
        $envoie = new Envoie();

        $form1 = $this->createForm(EnvoiType::class, $envoie);

        $form1->handleRequest($request);

        $form1->submit($values);


        $retrait = new Retrait();

        $form3 = $this->createForm(RetraiType::class, $retrait);

        $form3->handleRequest($request);

        $form3->submit($values);


        $trans = new Transaction();

        $form = $this->createForm(TransactionType::class, $trans);

        $form->handleRequest($request);

        $form->submit($values);

        $mois = date('m');
        $annee = date('Y');
        $seconde = date('s');

        $code = $mois . $seconde.$annee;

        $argent = $form->get('montant')->getData();

        $tarif = $this->getDoctrine()->getRepository(Tarifs::class)->findAll();

        foreach ($tarif as $values) {
            $values->getBorneInferieure();
            $values->getBorneSuperieure();
            $values->getValeur();
            if (
                $argent >= $values->getBorneInferieure()
                && $argent <= $values->getBorneSuperieure()
            ) {
                $trans->setTarif($values);
                $commission = $values->getValeur();

                $commi1 = ($commission * 10) / 100;
                $commi2 = ($commission * 20) / 100;
                $commi3 = ($commission * 30) / 100;
                $commi4 = ($commission * 40) / 100;
            }
        }

        $trans->setCommissionEnvoie($commi1);
        $trans->setCommissionRetrait($commi2);
        $trans->setCommissionEtat($commi3);
        $trans->setCommissionAdmin($commi4);


        $trans->setCodeEnvoie($code);
        $trans->setDateEnvoie(new \DateTime());
        $user = $this->getUser();

    $agence=$user->getPartenaire()->getEntreprise();

    $trans->setAgence($agence);
        $trans->setUser($user);
        $trans->setEnvoie($envoie);
        $trans->setRetrait($retrait);

        $trans->setStatus("Disponible");


        $compt = $this->getDoctrine()->getRepository(Compts::class)->findOneBy(['partenaire' => $user->getPartenaire()]);



        if ($compt->getSolde() > $trans->getMontant()) {
            $montantcal = $compt->getSolde() - $trans->getMontant() + $commi1;


            $compt->setSolde($montantcal);

            $entityManager->persist($trans);
            $entityManager->persist($envoie);
            $entityManager->persist($retrait);
            $entityManager->persist($compt);

            $entityManager->flush();

            $data = [
                'status' => 200,
                'message' =>  'Bienvenue chez wari !!'.$envoie->getPrenomEnvoyeur(). 
                ' '.$envoie->getNomEnvoyeur(). '  vous a envoyer: '.$trans->getMontant().' Fcf  Voici le code : '.$trans->getCodeEnvoie()
                
            ];
            return new JsonResponse($data);

        } 
        else {
            $data = [
                'status' => 500,
                'message' => 'Veiller revoir votre solde'
            ];
            return new JsonResponse($data);
        }
    }
    //------------------Retrait-----------------/////////

/**
 * @Route("/retrait", name="retrait", methods={"POST","GET"})
 * @IsGranted({"ROLE_USER", "ROLE_ADMIN"})
 */
public function retrait(Request $request, EntityManagerInterface $entityManager): Response

{

 $values = json_decode($request->getContent(),true); 

    $user = $this->getUser();

    $retrait= $this->getDoctrine()->getRepository(Transaction::class)->findOneBy(['codeEnvoie' => $values['codeEnvoie']]);   
         if(!$retrait){
             $data = [
            'statu' => 500,
            'Message' => 'Le code saisi est incorecte .Veuillez revoire le code  '
                ];
                return new JsonResponse($data);
     }

      else if($retrait->getCodeEnvoie()==$values['codeEnvoie'] && $retrait->getStatus()=="Retirer" ){
        $data = [
            'statu' => 400,
            'Messages' => 'Le code est déja retiré'
        ];
        return new JsonResponse($data);
  
                }
     
        $retrait->setDateretrait(new \DateTime());
        $retrait->setStatus("Retirer");
        $retrait->setCni($values['cni']);
     
        $retrait->setUser($user);
   
    $entityManager->persist($retrait);
    $entityManager->flush(); 
    $data = [
        'statu' => 200,
        'Message' => 'Retrait effectuer. Voici le montant:  '. $retrait->getMontant().'Fcf'
    ];
    return new JsonResponse($data);

}
/////----------------liste de personnes qui figure dans la table retrait---------////

/**
 * @Route("/listeretrait", name="testretrait", methods={"POST","GET"})
 * 
 */
public function testretrait(Request $request,RetraitRepository $retrait,SerializerInterface $serializer, EntityManagerInterface $entityManager): Response

{
    $values = json_decode($request->getContent(),true); 
    
    $listeuser = $retrait->findAll();
    $data = $serializer->serialize($listeuser, 'json', [
        'groups' => ['list']
    ]);
    return new Response($data, 200, [
        'Content-Type' => 'application/json'
    ]);
   
return new JsonResponse($data);
 
}

//-------------Cherche des infos du code d'envoi--------------------/////////////////
/**
 * @Route("/findcode",name="findcode",methods={"POST","GET"})
 */

public function findcodeEnvoi(Request $request,TransactionRepository $retrait,SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
{

 $values = json_decode($request->getContent(),true); 
    $testcode=$retrait->findByCodeEnvoie($values['codeEnvoie']);
    //var_dump($testcode);die();
    $testcode[0]->getRetrait();
    $data = $serializer->serialize($testcode[0], 'json', [
        'groups' => ['list']
    ]);
    return new Response($data, 200, [
        'Content-Type' => 'application/json'
    ]);
}
///////-------Liste de tous les transaction------///////

/**
 * @Route("/listeTrans",name="listeTrans",methods={"POST","GET"})
 * @IsGranted("ROLE_ADMIN")
 */
public function listTransaction(TransactionRepository $transRepo , SerializerInterface $serializer ):Response{
    $listTrans = $transRepo->findAll();
    $data = $serializer->serialize($listTrans, 'json', [
        'groups' => ['listTrans']
    ]);
    return new Response($data, 200, [
        'Content-Type' => 'application/json'
    ]);
}
///---------Liste des transaction par Users--------////

/**
 * @Route("/listeTransUser",name="listeTransUser",methods={"POST","GET"})
 * @IsGranted({"ROLE_USER", "ROLE_ADMIN"})
 * 
 */
public function listTransUser(TransactionRepository $transRepo , SerializerInterface $serializer ):Response{
    $userTrans=$this->getUser()->getId();
    $liste = $transRepo ->findBy(["user" =>  $userTrans]);
    $data = $serializer->serialize($liste, 'json', [
        'groups' => ['listUserTrans']
    ]);
    return new Response($data, 200, [
        'Content-Type' => 'application/json'
    ]);

}
 
}
