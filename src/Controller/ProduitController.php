<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produits')]
    public function index(EntityManagerInterface $em, Request $request, TranslatorInterface $translator): Response
    {
        $produit = new Produit();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produits');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            $em->persist($produit); // prepare l'insertion
            $em->flush(); // execute l'insertion

            $produit = new Produit();
            $form = $this->createForm(ProduitType::class, $produit);

            $this->addFlash('success', 'Produit ajouté !');
        }

        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'formulaire' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'un_produit')]
    public function produit(Produit $produit = null, Request $request, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Le produit est introuvable');
            return $this->redirectToRoute('app_produits');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produits');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            $em->persist($produit); // prepare l'insertion
            $em->flush(); // execute l'insertion

            $this->addFlash('success', 'Produit modifié !');

            return $this->redirectToRoute('app_produits');
        }

        return $this->render('produit/produit.html.twig', [
            'produit' => $produit,
            'edit' => $form->createView(),
        ]);
    }

    #[Route('delete/{id}', name: 'produit_delete')]
    public function delete(Produit $produit = null, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Le produit est introuvable');
            return $this->redirectToRoute('app_produits');
        }

        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé !');

        return $this->redirectToRoute('app_produits');
    }
}
