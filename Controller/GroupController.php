<?php

namespace Puzzle\Api\ContactBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\ContactBundle\Entity\Group;
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
class GroupController extends BaseFOSRestController
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
        $this->fields = ['name', 'description', 'parent'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/groups")
	 */
	public function getContactGroupsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Group::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/groups/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function getContactGroupAction(Request $request, Group $group) {
	    if ($group->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $group]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/groups")
	 */
	public function postContactGroupAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Group::class)->find($data['parent']) : null;
	    
	    /** @var Group $group */
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
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Group::class)->find($data['parent']) : null;
	    
	    /** @var Group $group */
	    $group = Utils::setter($group, $this->fields, $data);
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/groups/{id}/add-contacts")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function putContactGroupAddContactsAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    if (isset($data['contacts_to_add']) && count($data['contacts_to_add']) > 0) {
	        $contactsToAdd = $data['contacts_to_add'];
	        foreach ($contactsToAdd as $contact) {
	            $group->addContact($contact);
	        }
	        
	        $em = $this->doctrine->getManager($this->connection);
	        $em->flush();
	        
	        return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 304]));
	}
	
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/groups/{id}/remove-contacts")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function putContactGroupRemoveContactsAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    if (isset($data['contacts_to_remove']) && count($data['contacts_to_remove']) > 0) {
	        $contactsToRemove = $data['contacts_to_remove'];
	        foreach ($contactsToRemove as $contact) {
	            $group->removeContact($contact);
	        }
	        
	        $em = $this->doctrine->getManager($this->connection);
	        $em->flush();
	        
	        return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 304]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/groups/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("group", class="PuzzleApiContactBundle:Group")
	 */
	public function deleteContactGroupAction(Request $request, Group $group) {
	    $user = $this->getUser();
	    
	    if ($group->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    $em->remove($group);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}