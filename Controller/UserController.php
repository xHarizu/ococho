<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\PassType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController.
 *
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $userRepository;
    private $paginator;

    /**
     * UserController constructor.
     *
     * @param UserRepository     $userRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
    }

    /**
     * Index action.
     *
     * @param Request            $request
     * @param UserRepository     $userRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="user_index",
     * )
     */
    public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $userRepository->queryAll(),
            $request->query->getInt('page', 1),
            UserRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'user/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param int $id
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route(
     *     "/{id}",
     *     methods={"GET"},
     *     name="user_show",
     *     requirements={"id": "[1-9]\d*"},
     *)
     */
    public function show(int $id): Response
    {
        $users = $this->userRepository->findOneById($id);

        return $this->render(
            'user/show.html.twig',
            ['users' => $users]
        );
    }

    /**
     * Edit action.
     *
     * @param Request                      $request         HTTP request
     * @param User                         $user            User entity
     * @param UserRepository               $userRepository  User repository
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="user_edit",
     * )
     */
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $userRepository->save($user);
            $this->addFlash('success', 'user_updated_successfully');

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Edit Password Action.
     *
     * @param Request                      $request         HTTP request
     * @param User                         $user            User entity
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository               $repository      User repository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/change_password",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="password_edit",
     * )
     */
    public function editpass(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder, UserRepository $repository): Response
    {
        $form = $this->createForm(PassType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $repository->save($user);

            $this->addFlash('success', 'message.updated_successfully');

            return $this->redirectToRoute('user_show', array('id' => $this->getUser()->getId()));
        }

        return $this->render(
            'user/edit_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}
