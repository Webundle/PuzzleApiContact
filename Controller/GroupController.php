<?php

namespace Puzzle\Api\ContactBundle\Controller;

use Puzzle\Api\ContactBundle\Entity\Group;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Component\HttpFoundation\Request;
use Puzzle\Api\ContactBundle\Entity\Contact;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class GroupController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['name', 'description', 'parent'];
    }
    
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/groups")
	 */
	public function getContactGroupsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Group::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/groups/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function getContactGroupAction(Request $request, Group $group) {
	    if ($group->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $group));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/groups")
	 */
	public function postContactGroupAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Group::class)->find($data['parent']) : null;
	    
	    /** @var Puzzle\Api\ContactBundle\Entity\Group $group */
	    $group = Utils::setter(new Group(), $this->fields, $data);
	    
	    $em->persist($group);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $group]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/groups/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function putContactGroupAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Group::class)->find($data['parent']) : null;
	    
	    /** @var Puzzle\Api\ContactBundle\Entity\Group $group */
	    $group = Utils::setter($group, $this->fields, $data);
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $group));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/groups/{id}/add-contacts")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function putContactGroupAddContactsAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    $contactsToAdd = $data['contacts_to_add'] ? explode(',', $data['contacts_to_add']) : null;
	    if ($contactsToAdd !== null) {
	        /** @var Doctrine\ORM\EntityManager $em */
	        $em = $this->get('doctrine')->getManager($this->connection);
	        
	        foreach ($contactsToAdd as $contactId) {
	            $contact = $em->getRepository(Contact::class)->find($contactId);
	            $group->addContact($contact);
	        }
	        
	        
	        $em->flush();
	        
	        return $this->handleView(FormatUtil::formatView($request, $group));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
	
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/groups/{id}/remove-contacts")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function putContactGroupRemoveContactsAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    $contactsToRemove = $data['contacts_to_remove'] ? explode(',', $data['contacts_to_remove']) : null;
	    if ($contactsToRemove !== null) {
	        /** @var Doctrine\ORM\EntityManager $em */
	        $em = $this->get('doctrine')->getManager($this->connection);
	        
	        foreach ($contactsToRemove as $contactId) {
	            $contact = $em->getRepository(Contact::class)->find($contactId);
	            $group->removeContact($contact);
	        }
	        
	        $em->flush();
	        
	        return $this->handleView(FormatUtil::formatView($request, $group));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/groups/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function deleteContactGroupAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($group);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}