<?php
/**
 * Question controller.
 */

namespace App\Controller;

use App\DataFixtures\CategoryFixtures;
use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class QuestionController.
 *
 * @Route("/questions")
 */
class QuestionController extends AbstractController
{
    private $questionRepository;
    private $paginator;
    private $answer;
    private $render;
    private $answerRepository;

    /**
     * QuestionsController constructor.
     *
     * @param QuestionRepository $questionRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(QuestionRepository $questionRepository, PaginatorInterface $paginator)
    {
        $this->questionRepository = $questionRepository;
        $this->paginator = $paginator;
    }

    /**
     * Index action.
     *
     * @param Request            $request
     * @param QuestionRepository $questionRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="question_index",
     * )
     */
    public function index(Request $request, QuestionRepository $questionRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $questionRepository->queryAll(),
            $request->query->getInt('page', 1),
            QuestionRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'question/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param int              $id
     * @param Request          $request          HTTP request
     * @param Question         $question         Question entity
     * @param AnswerRepository $answerRepository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Route(
     *     "/{id}",
     *     methods={"POST", "GET"},
     *     name="question_show",
     *     requirements={"id": "[1-9]\d*"},
     *)
     */
    public function show(int $id, Request $request, Question $question, AnswerRepository $answerRepository): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        $id = $question->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setQuestion($question);
            $answerRepository->save($answer);

            return $this->redirectToRoute('question_show', ['id' => $id]);
        }

        $questions = $this->questionRepository->findOneById($id);

        return $this->render(
            'question/show.html.twig',
            ['questions' => $questions, 'answers' => $answerRepository->findAll(), 'form' => $form->createView()]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route(
     *     "/create",
     *     methods={"GET", "POST"},
     *     name="question_create",
     * )
     */
    public function create(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        dump($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setUser($this->getUser());
            $this->questionRepository->save($question);

            return $this->redirectToRoute(('question_index'));
        }

        return $this->render(
            'question/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request            $request            HTTP request
     * @param Question           $question           Question entity
     * @param QuestionRepository $questionRepository Question repository
     *
     * @return Response HTTP response
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route(
     *     "/{id}/edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="question_edit",
     * )
     */
    public function edit(Request $request, Question $question, QuestionRepository $questionRepository): Response
    {
        $form = $this->createForm(QuestionType::class, $question, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->save($question);

            $this->addFlash('success', 'message_updated_successfully');

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/edit.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request            $request            HTTP request
     * @param Question           $question           Question entity
     * @param QuestionRepository $questionRepository Question repository
     *
     *
     * @param AnswerRepository   $answerRepository
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Route(
     *     "/{id}/delete",
     *     methods={"GET", "DELETE"},
     *     requirements={"id": "[1-9]\d*"},
     *     name="question_delete",
     * )
     */
    public function delete(Request $request, Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(FormType::class, $question, ['method' => 'DELETE']);
        $form->handleRequest($request);

        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($question->getAnswer() as $answer) {
                $question->removeAnswer($answer);
                $answerRepository->delete($answer);
            }
            $questionRepository->delete($question);


            $this->addFlash('success', 'message.deleted_successfully');

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/delete.html.twig',
            [
                'form' => $form->createView(),
                'question' => $question,
            ]
        );
    }
}
