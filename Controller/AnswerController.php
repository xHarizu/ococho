<?php
/**
 * Answer Controller
 */

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerBestType;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Flex\PackageFilter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AnswerController.
 *
 * @Route("/answer")
 */

class AnswerController extends AbstractController
{
    private $answerRepository;
    private $answer;
    private $paginator;

    /**
     * AnswerController constructor
     *
     * @param AnswerRepository   $answerRepository Answer Repository
     * @param PaginatorInterface $paginator
     */
    public function __construct(AnswerRepository $answerRepository, PaginatorInterface $paginator)
    {
        $this->answerRepository = $answerRepository;
        $this->paginator = $paginator;
    }

    /**
     * Index action.
     *
     * @param Request            $request          HTTP request
     *
     * @param PaginatorInterface $paginator
     * @param AnswerRepository   $answerRepository
     *
     * @return Response               HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="answer_index",
     * )
     */
    public function index(Request $request, PaginatorInterface $paginator, AnswerRepository $answerRepository): Response
    {
        $pagination = $paginator->paginate(
            $answerRepository->queryAll(),
            $request->query->getInt('page', 1),
            AnswerRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'answer/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     *
     * Show Action
     *
     * @Route(
     *     "/{id}",
     *     methods={"GET"},
     *     name="answer_show",
     *     requirements={"id": "[1-9]\d*"},
     * )
     * @param  Answer $answer
     *
     * @return Response
     */
    public function show(Answer $answer): Response
    {
        return $this->render(
            'answer/show.html.twig',
            ['answer' => $answer]
        );
    }

    /**
     * Create action.
     *
     * @param Request          $request          HTTP request
     * @param AnswerRepository $answerRepository Answer repository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/create",
     *     methods={"GET", "POST"},
     *     name="answer_create",
     * )
     */
    public function create(Request $request, AnswerRepository $answerRepository): Response
    {
        $answer = new Answer();

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->save($answer);
            $this->addFlash('success', 'answer_created_successfully');

            return $this->redirectToRoute('answer_index');
        }

        return $this->render(
            'answer/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request          $request          HTTP request
     * @param Answer           $answer           Answer entity
     * @param AnswerRepository $answerRepository Answer repository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="answer_edit",
     * )
     */
    public function edit(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(AnswerType::class, $answer, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->save($answer);

            $this->addFlash('success', 'answer_updated_successfully');

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'answer/edit.html.twig',
            [
                'form' => $form->createView(),
                'answers' => $answer,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request          $request          HTTP request
     * @param Answer           $answer
     * @param AnswerRepository $answerRepository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="answer_delete",
     * )
     */
    public function delete(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(FormType::class, $answer);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->delete($answer);
            $this->addFlash('success', 'answer.deleted_successfully');

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'answer/delete.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }

    /**
     * Best action.
     *
     * @param Request          $request          HTTP request
     * @param Answer           $answer           Answer Entity
     * @param AnswerRepository $answerRepository Answer repository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}/best",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="answer_best",
     * )
     */
    public function best(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(AnswerBestType::class, $answer, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->save($answer);

            $this->addFlash('success', 'message_updated_successfully');

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'answer/best.html.twig',
            [
                'form' => $form->createView(),
                'answer' => $answer,
            ]
        );
    }
}
