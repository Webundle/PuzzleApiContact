<?php

namespace Puzzle\Api\ContactBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\ContactBundle\Entity\Contact;
use Puzzle\Api\MediaBundle\PuzzleApiMediaEvents;
use Puzzle\Api\MediaBundle\Event\FileEvent;
use Puzzle\Api\MediaBundle\Util\MediaUtil;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\ErrorFactory;
use Puzzle\OAuthServerBundle\Service\Repository;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class ContactController extends BaseFOSRestController
{
    /**
     * @param RegistryInterface         $doctrine
     * @param Repository                $repository
     * @param SerializerInterface       $serializer
     * @param EventDispatcherInterface  $dispatcher
     * @param ErrorFactory              $errorFactory
     */
    public function __construct(
        RegistryInterface $doctrine,
        Repository $repository,
        SerializerInterface $serializer,
        EventDispatcherInterface $dispatcher,
        ErrorFactory $errorFactory
    ){
        parent::__construct($doctrine, $repository, $serializer, $dispatcher, $errorFactory);
        $this->fields = ['firstName', 'lastName', 'civility', 'phone', 'email', 'location', 'company', 'position'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/contacts")
	 */
	public function getContactsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Contact::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/contacts/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("contact", class="PuzzleApiContactBundle:Contact")
	 */
	public function getContactAction(Request $request, Contact $contact) {
	    if ($contact->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $contact]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/contacts")
	 */
	public function postContactAction(Request $request) {
	    $data = $request->request->all();
	    /** @var Contact $contact */
	    $contact = Utils::setter(new Contact(), $this->fields, $data);
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
		$em->persist($contact);
		/* Add picture */
		if (isset($data['picture']) && $data['picture']) {
		    $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
		        'path'        => $data['picture'],
		        'folder'      => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Contact::class),
		        'user'        => $this->getUser(),
		        'closure'     => function($filename) use ($contact){$contact->setPicture($filename);}
		    ]));
		}
		
		$em->flush();
		
		return $this->handleView(FormatUtil::formatView($request, ['resources' => $contact]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/contacts/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("contact", class="PuzzleApiBundle:Contact")
	 */
	public function putContactAction(Request $request, Contact $contact) {
	    $user = $this->getUser();
	    
	    if ($contact->getCreatedBy()->getId() !== $user->getId()){
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    /** @var Contact $contact */
	    $contact = Utils::setter($contact, $this->fields, $data);
		
	    if (isset($data['picture']) && $data['picture'] !== $contact->getPicture()) {
		    $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
		        'path'        => $data['picture'],
		        'folder'      => $data['uploadDir'] ?? MediaUtil::extractContext(Contact::class),
		        'user'        => $user,
		        'closure'     => function($filename) use ($contact){$contact->setPicture($filename);}
		    ]));
		}
		
		/** @var Doctrine\ORM\EntityManager $em */
		$em = $this->doctrine->getManager($this->connection);
		$em->flush();
		
		return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));	
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/contacts/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("contact", class="PuzzleApiContactBundle:Contact")
	 */
	public function deleteContactAction(Request $request, Contact $contact) {
	    $user = $this->getUser();
	    
	    if ($contact->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
		$em = $this->doctrine->getManager($this->connection);
		$em->remove($contact);
		$em->flush();
		
		return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}