<?php

namespace Plugin\NewsUpgrade\Form\Extension;

use Eccube\Form\Type\Admin\NewsType;
use Eccube\Common\EccubeConfig;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\Validator\Constraints as Assert;

class TintucTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $options = $builder->get('description')->getOptions();
        $options['constraints'] = [
            new Assert\Length(['max' => $this->eccubeConfig['eccube_lltext_len']]),
        ];

        $builder->add('description', TextareaType::class, $options);
        $builder->add('tt_thumbnail_data', FileType::class, [
            'label' => 'Thumbnail Image',
            'required' => false,
            'eccube_form_options' => [
                'auto_render' => true,
            ],
            'mapped' => false,
        ]);

        $builder->add('tt_thumbnail_url', HiddenType::class, [
            'required' => false,
            'eccube_form_options' => [
                'auto_render' => true,
            ],
        ]);

        $builder
            ->add('ttseo_title', TextType::class, [
                'label' => '[SEO] Title',
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
                'constraints' => [
                    new Assert\Length(['max' => 60]),
                ],
        ]);
        $builder
            ->add('ttseo_description', TextareaType::class, [
                'label' => '[SEO] Description',
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
                'constraints' => [
                    new Assert\Length(['max' => 320]),
                ],
        ]);
        
        $builder
            ->add('ttseo_robots', ChoiceType::class, [
                'label' => '[SEO] Tag index',
                'choices' => [
                    'yes' => "index,follow",
                    'no' => "noindex,nofollow",
                ],
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
        ]);

    }

    public function getExtendedType()
    {
        return NewsType::class;
    }

    /**
    * Return the class of the type being extended.
    */
    public static function getExtendedTypes(): iterable
    {
        yield NewsType::class;
    }

}