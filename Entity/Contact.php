<?php

namespace Puzzle\Api\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;

use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Doctrine\Common\Collections\Collection;
use Puzzle\Api\ContactBundle\Entity\Group;

/**
 * Group
 *
 * @ORM\Table(name="contact")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("group")
 * @Hateoas\Relation(
 * 		name = "self", 
 * 		href = @Hateoas\Route(
 * 			"get_contact", 
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 */
class Contact
{
    use PrimaryKeyable,
        Timestampable,
        Blameable,
        Pictureable;
    
    /**
    * @ORM\Column(name="first_name", type="string", length=255)
    * @JMS\Expose
    * @JMS\Type("string")
    */
    private $firstName;
  
    /**
    * @ORM\Column(name="last_name", type="string", length=255)
    * @JMS\Expose
    * @JMS\Type("string")
    */
    private $lastName;
  
    /**
    * @ORM\Column(name="civility", type="string", length=255, nullable=true)
    * @JMS\Expose
    * @JMS\Type("string") 
    */
    private $civility;
  
    /**
    * @ORM\Column(name="phone", type="string", length=255, nullable=true)
    * @JMS\Expose
    * @JMS\Type("string")
    */
    private $phone;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @JMS\Expose
	 * @JMS\Type("string")
     */
    private $email;
    
    /**
     * @var string
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     * @JMS\Expose
	 * @JMS\Type("string")
     */
    private $location;
    
    /**
     * @var string
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     * @JMS\Expose
	 * @JMS\Type("string")
     */
    private $company;
    
    /**
     * @var string
     * @ORM\Column(name="position", type="string", length=255, nullable=true)
     * @JMS\Expose
	 * @JMS\Type("string")
     */
    private $position;
    
    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="groups")
     * @ORM\JoinTable(name="contact_groups",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private $groups;
    
    public function setFirstName($firstName) : self {
        $this->firstName = $firstName;
        return $this;
    }
    
    public function getFirstName() :? string {
        return $this->firstName;
    }
    
    public function setLastName($lastName) : self {
        $this->lastName = $lastName;
        return $this;
    }
    
    public function getLastName() :? string {
        return $this->lastName;
    }
    
    public function setEmail($email) : self {
        $this->email = $email;
        return $this;
    }

    public function getEmail() :? string {
        return $this->email;
    }
    
    public function setPhone($phone) : self {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone() :? string {
        return $this->phone;
    }

    public function setLocation($location) : self {
        $this->location = $location;
        return $this;
    }

    public function getLocation() :?string {
        return $this->location;
    }

    public function setCompany($company) :self {
        $this->company = $company;
        return $this;
    }

    public function getCompany() :?string {
        return $this->company;
    }

    public function setPosition($position) :self {
        $this->position = $position;
        return $this;
    }

    public function getPosition() :?string {
        return $this->position;
    }
    
    public function setCivility($civility) :self {
        $this->civility = $civility;
        return $this;
    }
    
    public function getCivility() :?string {
        return $this->civility;
    }
    
    public function getFullName() :?string {
        return trim($this->firstName. ' '. $this->lastName);
    }
    
    public function setGroups (Collection $groups) : self {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
        
        return $this;
    }
    
    public function addGroup(Group $group) :self {
        if ($this->groups->count() === 0 || $this->groups->contains($group) === false) {
            $this->groups->add($group);
        }
        
        return $this;
    }
    
    public function removeGroup(Group $group) :self {
        if ($this->groups->contains($group) === true) {
            $this->groups->removeElement($group);
        }
        
        return $this;
    }
    
    public function getGroups() :?Collection {
        return $this->groups;
    }
}
