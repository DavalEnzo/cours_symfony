<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        // Création d'un objet vide pour le formulaire
        $categorie = new Categorie();
        // Importation du formulaire en lui passant l'objet créé précédemment
        $form = $this->createForm(CategorieType::class, $categorie);

        // On demande au formulaire d'analyser la requête HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie); // prepare l'insertion
            $em->flush(); // execute l'insertion

            $categorie = new Categorie();
            $form = $this->createForm(CategorieType::class, $categorie);

            $this->addFlash('success', 'Catégorie est ajoutée');
        }

        $categories = $em->getRepository(Categorie::class)->findAll();
        /**
         * Il existe plusieurs méthodes pour interagir avec les tables :
         * findAll()  -> récupère toute la table
         * find($id)  -> récupère la ligne dont l'id est $id
         * findBy(['colonne' => 'valeur']) -> récupère toute la table avec une clause WHERE
         * findOneBy(['colonne' => 'valeur']) : récupère une ligne dans la table avec une clause WHERE.
         */

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'formulaire' => $form->createView(), // Envoi du formulaire à la vue
        ]);
    }

    #[Route('/categorie/{id}', name: 'une_categorie')]
    public function categorie(Categorie $categorie = null, Request $request, EntityManagerInterface $em)
    {

        if ($categorie == null) {
            $this->addFlash('danger', 'La catégorie est introuvable');
            return $this->redirectToRoute('app_categorie');
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie); // prepare l'insertion
            $em->flush(); // execute l'insertion

            $this->addFlash('success', 'La catégorie a été mise à jour');

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/categorie.html.twig', [
            'categorie' => $categorie,
            'edit' => $form->createView(), // Envoi du formulaire à la vue
        ]);
    }

    #[Route('/categorie/delete/{id}', name: 'categorie_delete')]
    public function delete(Categorie $categorie = null, EntityManagerInterface $em, Request $request)
    {
        if ($categorie == null) {
            $this->addFlash('danger', 'La catégorie est introuvable');
            return $this->redirectToRoute('app_categorie');
        }

        $em->remove($categorie);
        $em->flush();
        $this->addFlash('warning', 'La catégorie supprimée');
        return $this->redirectToRoute('app_categorie');
    }
}
