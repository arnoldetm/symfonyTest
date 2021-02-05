<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class CategoryController extends Controller
{
    /**
     * @Route("/category", name="list_categories")
     */
    public function indexAction(Request $request)
    {
        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')->findAll();
        return $this->render('category/list.html.twig', array("categories"=>$categories));
    }
    /**
     * @Route("/category/create", name="create_categories")
     */
    public function createAction(Request $request)
    {
        $category = new Category;
        $form = $this->createFormBuilder($category)
        ->add('name', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese Nombre Categoría')))
        ->add('active', ChoiceType::class, array('choices'=>array('Seleccione..'=>'', 'Activo'=>1, 'Inactivo'=>0),'attr'=>array('class'=>'form-control')))
        ->add('save', SubmitType::class, array('attr' => array('class'=>'d-none', 'id'=>'save_category')))
        ->getForm();

        $form->handleRequest($request);

        if($form -> isSubmitted()&&$form->isValid()){
            $name = $form['name']->getData();
            $active = $form['active']->getData();
            $category->setName($name);
            $category->setActive($active);
            $data = $this->getDoctrine()->getManager();
            $data->persist($category);
            try {
                $data->flush(); 
            } catch (\Throwable $th) {
                return $this->redirectToRoute('list_categories');
            }
            
            return $this->redirectToRoute('list_categories');
        }

        return $this->render('category/form.html.twig', array('form'=>$form->createView()));
    }
    /**
     * @Route("/category/edit/{id}", name="edit_categories")
     */
    public function editAction($id,Request $request)
    {
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);

        $category->setName($category->getName());
        $category->setActive($category->getActive());

        $form = $this->createFormBuilder($category)
        ->add('name', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese Nombre Categoría')))
        ->add('active', ChoiceType::class, array('choices'=>array('Seleccione..'=>'', 'Activo'=>1, 'Inactivo'=>0),'attr'=>array('class'=>'form-control')))
        ->add('save', SubmitType::class, array('attr' => array('class'=>'d-none', 'id'=>'save_category')))
        ->getForm();

        $form->handleRequest($request);

        if($form -> isSubmitted()&&$form->isValid()){
            $name = $form['name']->getData();
            $active = $form['active']->getData();

            $data = $this->getDoctrine()->getManager();
            $category  = $data->getRepository('AppBundle:Category')->find($id);

            $category->setName($name);
            $category->setActive($active);

            try {
                $data->flush(); 
            } catch (\Throwable $th) {
                return $this->redirectToRoute('list_categories');
            }

            return $this->redirectToRoute('list_categories');
        }
         
        return $this->render('category/form.html.twig', array('form'=>$form->createView()));
    }
    /**
     * @Route("/category/delete/{id}", name="delete_categories")
     */
    public function delete($id)
    {
        $data = $this->getDoctrine()->getManager();
        
        $category  = $data->getRepository('AppBundle:Category')->find($id);
        
        $data->remove($category);
        
        try {
            $data->flush(); 

        } catch (\Throwable $th) {

        }
        return $this->redirectToRoute('list_categories');

    }
    /**
     * @Route("/category/mailer", name="send_categories")
     */
    public function SendEmail(\Swift_Mailer $mailer)
    {
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();

        $message = (new \Swift_Message('Hello Email'))
        ->setFrom('symfonyp@gmail.com')
        ->setTo('arnoldetm@gmail.com.com')
        ->setBody(
            $this->renderView(
                'products/mailer.html.twig', array("products"=>$products)),
                'text/html'
            );
        

        $mailer->send($message);

        // or, you can also fetch the mailer service this way
        // $this->get('mailer')->send($message);


        return $this->redirectToRoute('list_categories');
    }

}
