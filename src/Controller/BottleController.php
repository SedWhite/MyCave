<?php

namespace App\Controller;

use App\Entity\Bottle;
use App\Form\BottleType;
use App\Repository\BottleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class BottleController extends AbstractController
{
    // -- ADD BOTTLE --
    #[Route('/bottleAdd', name: 'app_bottle_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, BottleRepository $bottleManager): Response
    {
        $bottle = new Bottle();
        $form = $this->createForm(BottleType::class, $bottle);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $bottleManager->add($bottle);
            $this->addFlash('success', 'La bouteille a bien été ajoutée');
            return $this->redirectToRoute('app_bottle_seeAll');
        }
        
        return $this->render('bottle/addBottle.html.twig', ['form' => $form->createView()]);
    }
    
    // -- SEE ALL BOTTLES --
    #[Route('/toutes-les-bouteilles', name: 'app_bottle_seeAll')]
    public function seeAll(BottleRepository $bottleManager): Response
    {
        $bottles = $bottleManager->findAll();
        
        return $this->render('bottle/seeAll.html.twig', ['bottles' => $bottles]);
    }

    // -- SEE PER COUNTRY --
    #[Route('/bouteilles/{country}', name: 'app_bottle_seePerCountry')]
    public function seePerCountry(string $country, BottleRepository $bottleManager): Response
    {
        $otherCountry = $bottleManager->findOtherCountry();
        $navCountry = ['USA','France'];

        if(in_array($country, $navCountry)){
            $bottles = $bottleManager->findBy(['country' => $country]);
            $countryName = $country;
        }else{
            $bottles = [];
            foreach($otherCountry as $actualCountry){
                $bottles = array_merge($bottles,$bottleManager->findBy(['country' => $actualCountry]));
                $countryName = 'Autres pays';
            }
        }

        // $bottles = $bottleManager->findAll();
        
        return $this->render('bottle/seePerCountry.html.twig', ['bottles' => $bottles, 'countryName' => $countryName]);
    }
    
    // -- REMOVE BOTTLE --
    #[Route('/bottleDelete/{id<\d+>}', name: 'app_bottle_delete')]
    #[IsGranted('ROLE_USER')]
    public function delete(Bottle $bottle, BottleRepository $bottleManager, Request $request): Response
    {
        //On récupère le token envoyer par le formulaire
        $csrf_token = $request->request->get('token');

        //On vérifie si il correspond à celui de la session courante
        if($this->isCsrfTokenValid('delete-bottle', $csrf_token)){
            $bottleManager->remove($bottle);
            $this->addFlash('success', 'La bouteille a bien été supprimée');
            return $this->redirectToRoute('app_bottle_seeAll');
        }
        
        $this->addFlash('error', 'Le csrf Token est invalide');
        return $this->redirectToRoute('app_bottle_seeAll');
    }
    
    // -- EDIT BOTTLE --
    #[Route('/bottleEdit/{id<\d+>}', name: 'app_bottle_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Bottle $bottle, Request $request, BottleRepository $bottleManager): Response
    {
        $form = $this->createForm(BottleType::class, $bottle);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $bottleManager->add($bottle);
            $this->addFlash('success', 'La bouteille a bien été modifiée');
            return $this->redirectToRoute('app_bottle_seeAll');
        }
        
        return $this->render('bottle/addBottle.html.twig', ['form' => $form->createView()]);
    }
}
