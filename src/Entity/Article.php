<?php

namespace App\Entity;

use App\Attribute\ApiAuthGroups;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\UserOwnedInterface;
use App\Controller\EmptyController;
use App\Repository\ArticleRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Controller\ArticleCountController;
use App\Controller\ArticleImageController;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ArticlePublishController;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *   normalizationContext={
 *           "groups"={"article:read"},
 *            "swagger_definition_name" = "article:coll-teste"
 *      },
 *   denormalizationContext={ "groups"={"article:write"} },
 *   paginationItemsPerPage = 2,
 *   paginationMaximumItemsPerPage = 6,
 *   paginationClientItemsPerPage = true,
 *   collectionOperations={
 *      "get"={ 
 *               "security" = "is_granted('ROLE_USER')",
 *               "openapi_context" = { "security" = {{ "bearerAuth" = {} }}},
 *         },
 *      "post" = {
 *           "security"="is_granted('ROLE_USER')",
 *           "validation_groups" = {"postValidation"},
 *           "openapi_context" = { "security" = {{ "bearerAuth" = {} }}},
 *         },
 *      "count" = {
 *         "security" = "is_granted('ROLE_USER')",
 *         "method" = "GET",
 *         "path" = "/articles/count",
 *         "controller" = ArticleCountController::class,
 *         "read" = false,
 *         "pagination_enabled" = false,
 *         "filters" = {},
 *         "openapi_context" = {
 *              "security" = {{ "bearerAuth" = {} }},
 *               "summary" = "Récupère le nombre total d\'article",
 *               "parameters" = {
 *                 {
 *                   "name" = "is_published",
 *                   "in" = "query",
 *                   "schema" = {
 *                       "type" = "integer",
 *                       "maximum" = 1,
 *                       "minimum" = 0,
 *                   },
 *                   "description" = "Filtre les article en ligne"
 *                  }
 *               },
 *               "responses" = {
 *                    "200" = {
 *                       "description"  = "OK",
 *                       "application/json"  = {
 *                          "schema" = {
 *                             "type" = "integer",
 *                             "example" = 3
 *                              }
 *                           }
 *                      }
 *                  }
 *          }
 *       }
 *      },
 *   itemOperations={
 *      "get" = {
 *          "swagger_definition_name" = "article:detail-teste",
 *          "normalization_context" = {"groups"={"article:read:item"}},
 *          "openapi_context" = { "security" = {{ "bearerAuth" = {} }}},
 *      },
 *      "put" = {
 *          "security"="is_granted('edit', object)",
 *          "validation_groups" = {"putValidation"}
 *      },
 *      "delete" = {"security"="is_granted('delete', object)"},
 *      "patch",
 *      "publish" = {
 *        "method" = "POST",
 *        "path" = "/posts/{id}/publish",
 *        "controller" = ArticlePublishController::class,
 *         "openapi_context" = {
 *               "summary" = "PUBLIER UN article",
 *               "requestBody" = {
 *                   "content" = { "application/json" = { "schema" = { "type" = "object", "properties" = {}}}}
 *               },
 *               "responses" = {
 *                   "201" = { "description" = "article published" ,"application/json" = { "schema" = { "type" = "object", "properties" = {}}}}
 *               }
 *          }
 *      },
 *     "image" = {
 *      "method" = "POST",
 *      "path" = "/articles/{id}/image",
 *      "controller" = EmptyController::class,
 *      "openapi_context" = {
 *          "security" = {{ "bearerAuth" = {} }},
 *          "requestBody" = { "content" =  { "multipart/form-data" = { "schema" = {
 *               "type" = "object",
 *               "properties" = { "file" = { "type" = "string" , "format" = "binary"  }},
 *            
 *           }} }}
 *        }
 *       
 * 
 * 
 *      }
 * 
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties = {"id" = "exact", "title" = "partial", "content" = "partial"}
 * )
 *@ApiFilter(
 *      OrderFilter::class,
 *      properties = {"id" = "ASC", "title" = "DESC"}
 * )
 * 
 *#@ApiAuthGroups(
 *#    CAN_EDIT = {"read:collection:Owner"},
 *#   ROLE_USER = {"read:collection:User"}
 *# )
 * 
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @Vich\Uploadable()
 */
class Article implements UserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"article:read", "article:read:item","user:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 5,
     *     max = 50,
     *     groups={"postValidation"}
     * )
     *@Assert\Length(
     *     min = 10,
     *     max = 70,
     *     groups={"putValidation"}
     * )
     * @Groups({"article:read","article:read:item", "article:read:item", "article:write","user:read"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"article:read", "read:collection:User"})
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"article:read", "article:write"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"article:read", "article:write"})
     */
    private $picture;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"article:read","read:collection:Owner"})
     * @ApiProperty( 
     *   openapiContext = { 
     *      "type" = "boolean",
     *      "description" = "En ligne ou pas ?"
     * })
     */
    private $isPublished;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("article:read")
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups("article:read")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("article:read")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="article", orphanRemoval=true)
     * @ApiSubresource(maxDepth=0)
     * @Groups("article:read:item")
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="articles", cascade={"persist"})
     * @Assert\Valid
     * @Groups({"article:read:item", "article:write"})
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("article:read")
     */
    private $author;



    /**
     * @var File|null
     * @Vich\UploadableField(mapping="post_image", fileNameProperty="filePath")
     * @Groups("article:write")
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read:collection","article:read"})
     */
    private $filePath;

    /**
     * @var string|null
     * @Groups({"read:collection","article:read"})
     */
    private $fileUrl;


    public function __construct()
    {
        $this->isPublished = false;
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }



    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return Article
     */
    public function setFile(?File $file): Article
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    /**
     * @param string|null $fileUrl
     * @return Post
     */
    public function setFileUrl(?string $fileUrl): Article
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }
}
