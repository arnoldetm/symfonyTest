<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ProductsController extends Controller
{
    /**
     * @Route("/products", name="list_products")
     */
    public function indexAction(Request $request)
    {
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();
        return $this->render('products/list.html.twig', array("products"=>$products));
    }
    /**
     * @Route("/products/create", name="create_products")
     */
    public function createAction(Request $request)
    {
        $product = new Product;
        $conn = $this->getDoctrine()->getEntityManager()->getConnection();
        $query = ' SELECT c.name, c.id  FROM category c  WHERE c.active = 1 ORDER BY c.name ASC ';
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $categories= $stmt->fetchAll();
        $categories = array_column($categories, 'id', 'name');

        $form = $this->createFormBuilder($product)
        ->add('code', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese código', "minlength"=>"4","maxlength"=>"10", "oninput"=>"this.value=this.value.replace(/[^ñÑA-Za-z0-9]/g,'');")))
        ->add('name', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese nombre')))
        ->add('description', TextareaType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese descripción')))
        ->add('brand', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese marca')))
        ->add('category', ChoiceType::class, array('choices'=>$categories,'attr'=>array('class'=>'form-control')))
        ->add('price',TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese precio', "maxlength"=>"12", "oninput"=>"Mask(this.value);")))
        ->add('save', SubmitType::class, array('attr' => array('class'=>'d-none', 'id'=>'save_product')))
        ->getForm();

        $form->handleRequest($request);

        if($form -> isSubmitted()&&$form->isValid()){

            $code = $form['code']->getData();
            $name = $form['name']->getData();
            $description = $form['description']->getData();
            $brand = $form['brand']->getData();
            $category = $form['category']->getData();
            $price = (int)preg_replace('/\./', '',  $form['price']->getData());
        
            $product->setCode($code);
            $product->setName($name);
            $product->setDescription($description);
            $product->setBrand($brand);
            $product->setCategory($category);
            $product->setPrice($price);

            
            $data = $this->getDoctrine()->getManager();
            $data->persist($product);
            try {
                $data->flush(); 
            } catch (\Throwable $th) {
                return $this->redirectToRoute('list_products');
            }
            
            return $this->redirectToRoute('list_products');
        }

        return $this->render('products/form.html.twig', array('form'=>$form->createView()));
    }
    /**
     * @Route("/products/edit/{id}", name="edit_products")
     */
    public function editAction($id,Request $request)
    {
        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $conn = $this->getDoctrine()->getEntityManager()->getConnection();
        $query = ' SELECT c.name, c.id  FROM category c WHERE c.active = 1 ORDER BY c.name ASC ';
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $categories= $stmt->fetchAll();
        $categories = array_column($categories, 'id', 'name');

        $product->setCode($product->getCode());
        $product->setName($product->getName());
        $product->setDescription($product->getDescription());
        $product->setBrand($product->getBrand());
        $product->setCategory($product->getCategory());
        $product->setPrice(number_format($product->getPrice(),0,",","."));

        $form = $this->createFormBuilder($product)
        ->add('code', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese código', "minlength"=>"4","maxlength"=>"10", "oninput"=>"this.value=this.value.replace(/[^ñÑA-Za-z0-9]/g,'');")))
        ->add('name', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese nombre')))
        ->add('description', TextareaType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese descripción')))
        ->add('brand', TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese marca')))
        ->add('category', ChoiceType::class, array('choices'=> $categories,'attr'=>array('class'=>'form-control')))
        ->add('price',TextType::class, array('attr'=>array('class'=>'form-control', 'placeholder'=>'Ingrese precio', "maxlength"=>"12", "oninput"=>"Mask(this.value);")))
        ->add('save', SubmitType::class, array('attr' => array('class'=>'d-none', 'id'=>'save_product')))
        ->getForm();

        $form->handleRequest($request);

        if($form -> isSubmitted()&&$form->isValid()){
            $code = $form['code']->getData();
            $name = $form['name']->getData();
            $description = $form['description']->getData();
            $brand = $form['brand']->getData();
            $category = $form['category']->getData();
            $price = (int)preg_replace('/\./', '',  $form['price']->getData());

            $data = $this->getDoctrine()->getManager();
            $product  = $data->getRepository('AppBundle:Product')->find($id);

            $product->setCode($code);
            $product->setName($name);
            $product->setDescription($description);
            $product->setBrand($brand);
            $product->setCategory($category);
            $product->setPrice($price);

            try {
                $data->flush(); 
            } catch (\Throwable $th) {
                return $this->redirectToRoute('list_products');
            }

            return $this->redirectToRoute('list_products');
        }
         
        return $this->render('products/form.html.twig', array('form'=>$form->createView()));
    }
    /**
     * @Route("/products/delete/{id}", name="delete_products")
     */
    public function delete($id,Request $request)
    {
        $data = $this->getDoctrine()->getManager();
        
        $product  = $data->getRepository('AppBundle:Product')->find($id);
        
        $data->remove($product);
        
        try {
            $data->flush(); 

        } catch (\Throwable $th) {

        }
        return $this->redirectToRoute('list_products');
    }
    /**
     * @Route("/products/mailer", name="send_products")
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


        return $this->redirectToRoute('list_products');
    }

}
