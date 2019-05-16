<?php


namespace App\Form;



use App\Entity\QuoteAuthor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;


class AuthorType extends AbstractType
{
    private $em;

    /**
     * AuthorType constructor.
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event)  {
                // Need to find Author from repo
                $data = $event->getData();
                $existingAuthor = $this->em->getRepository(QuoteAuthor::class)
                    ->findOneBy(['name'=>$data->getName()]);
                if ($existingAuthor instanceof QuoteAuthor) $event->setData($existingAuthor);
            }
        );

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => QuoteAuthor::class,
            'csrf_protection' => false,
            'validation_groups'=> 'Default'
        ));
    }


}
