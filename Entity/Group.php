<?php

namespace Puzzle\Api\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Puzzle\OAuthServerBundle\Traits\Describable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Puzzle\OAuthServerBundle\Traits\ExprTrait;

/**
 * Contact Group
 *
 * @ORM\Table(name="contact_group")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("contact_group")
 * @Hateoas\Relation(
 * 		name = "self", 
 * 		href = @Hateoas\Route(
 * 			"get_contact_group", 
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 * 
 * @Hateoas\Relation(
 *     name = "parent",
 *     embedded = "expr(object.getParent())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getParent() === null)"),
 *     href = @Hateoas\Route(
 * 			"get_contact_group", 
 * 			parameters = {"id" = "expr(object.getParent().getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "childs",
 *     embedded = "expr(object.getChilds())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getChilds() === null)")
 * ))
 * @Hateoas\Relation(
 * 		name = "contacts", 
 *      exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getContacts() === null)"),
 * 		href = @Hateoas\Route(
 * 			"get_contacts", 
 * 			parameters = {"id" = "=:~expr(object.stringify(',',object.getContacts()))"},
 * 			absolute = true,
 * ))
 */
class Group
{
	use PrimaryKeyable,
	    Describable,
	    Nameable,
	    Blameable,
	    Timestampable,
	    ExprTrait;
    /**
     * @var array
     * @ORM\Column(name="contacts", type="array", nullable=true)
     * @JMS\Expose
	 * @JMS\Type("array")
     */
    private $contacts;
    
    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="parent", cascade={"remove"})
     */
    private $childs;
    
    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="childs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;
    
    public function __construct() {
        $this->childs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function setContacts($contacts) :self {
    	foreach ($contacts as $contact){
    	    $this->addContact($contact);
    	}
    	
    	return $this;
    }
    
    public function addContact($contact) :self {
    	$this->contacts[] = $contact;
    	$this->contacts = array_unique($this->contacts);
    	
    	return $this;
    }
    
    public function removeContact($contact) :self {
    	$this->contacts = array_diff($this->contacts, [$contact]);
    	return $this;
    }
    
    public function getContacts() :?array {
        return $this->contacts;
    }

    public function addChild(Group $child) :self {
        $this->childs[] = $child;
        return $this;
    }

    public function removeChild(Group $child) :self {
        $this->childs->removeElement($child);
        return $this;
    }

    public function getChilds() :?Collection {
        return $this->childs;
    }

    public function setParent(Group $parent = null) :self {
        $this->parent = $parent;
        return $this;
    }

    public function getParent() :?Group {
        return $this->parent;
    }
}
