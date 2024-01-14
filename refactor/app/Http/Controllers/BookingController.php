<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Support\Arr;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
	 *
     * @var BookingRepository
     */
    protected $bookingRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request): Response
    {
		$user_id = $request->get('user_id');
		$userType = $request->__authenticatedUser->user_type;
		
        if (!empty($user_id)) {
            $response = $this->bookingRepository->getUsersJobs($user_id);
        } elseif ($userType == env('ADMIN_ROLE_ID') || $userType == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->bookingRepository->getAll($request);
        } else {
			$response = [];
		}

        return response($response);
    }

    /**
	 *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show($id): Response
    {
        $job = $this->bookingRepository
		            ->with('translatorJobRel.user')
		            ->find($id);

        return response($job);
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request): Response
    {
        $data = $request->all();
		$authenticatedUser = $request->__authenticatedUser;
        $response = $this->bookingRepository
		                 ->store($authenticatedUser, $data);

        return response($response);
    }

    /**
	 *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update($id, Request $request): Response
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
		$job = Arr::except($data, [
			'_token',
			'submit'
		]);
        $response = $this->bookingRepository->updateJob($id, $job, $cuser);

        return response($response);
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function immediateJobEmail(Request $request): Response
    {
		$data = $request->all();
        $response = $this->bookingRepository->storeJobEmail($data);

        return response($response);
    }

    /**
	 *
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
		$user_id = $request->get('user_id');
		
        if (!empty($user_id)) {
            $response = $this->bookingRepository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function acceptJob(Request $request): Response
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->bookingRepository->acceptJob($data, $user);

        return response($response);
    }

	/**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function acceptJobWithId(Request $request): Response
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->bookingRepository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cancelJob(Request $request): Response
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->bookingRepository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
	 *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function endJob(Request $request): Response
    {
        $data = $request->all();
        $response = $this->bookingRepository->endJob($data);

        return response($response);
    }

	/**
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function customerNotCall(Request $request): Response
    {
        $data = $request->all();
        $response = $this->bookingRepository->customerNotCall($data);

        return response($response);
    }

    /**
	 * Fetches all potential jobs of a user.
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPotentialJobs(Request $request): Response
    {
        $user = $request->__authenticatedUser;
        $response = $this->bookingRepository->getPotentialJobs($user);

        return response($response);
    }

	/**
     *
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = data_get($data, 'distance', "");
        $time = data_get($data, 'time', "");
		$jobid = data_get($data, 'jobid', "");
        $session = data_get($data, 'session_time', "");
		$manually_handled = ($data['manually_handled'] == 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] == 'true')? 'yes' : 'no';
        $admincomment = data_get($data, 'admincomment', "");
		
        if ($data['flagged'] == 'true') {
            if ($data['admincomment'] == '') {
				$flagged = 'yes';
				return "Please, add comment";
			}
        } else {
            $flagged = 'no';
        }
        
        if (!empty($time) || !empty($distance)) {
            Distance::where('job_id', '=', $jobid)->update([
				'distance' => $distance,
				'time'     => $time
			]);
        }

        if (!empty($admincomment) || !empty($session) || !empty($flagged) || !empty($manually_handled) || !empty($by_admin)) {
            Job::where('id', '=', $jobid)->update([
				'admin_comments'   => $admincomment,
				'flagged'          => $flagged,
				'session_time'     => $session,
				'manually_handled' => $manually_handled,
				'by_admin'         => $by_admin
			]);
        }

        return response('Record updated!');
    }

	/**
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function reOpen(Request $request): Response
    {
        $data = $request->all();
        $response = $this->bookingRepository->reOpen($data);

        return response($response);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
		$jobid = data_get($data, 'jobid');
        $job = $this->bookingRepository->find($jobid);
        $job_data = $this->bookingRepository->jobToData($job);
        
        try {
            $this->bookingRepository->sendNotificationTranslator($job, $job_data, '*');
            $message = 'SMS sent';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        } finally {
			return response(['success' => $message]);
		}
    }
}
