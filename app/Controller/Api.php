<?php
namespace Controller;
use Model\Subdivision;
use Model\Subscriber;
use Model\Telephone;
use Model\Access;
use Model\User;
use Model\File;
use Src\Request;
use Src\View;

class Api
{
    public function index(): void
    {
        $subdivisions = Subdivision::all()->toArray();
        (new View())->toJSON($subdivisions);
    }


    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }


    public function api_token(Request $request): void
    {
        $token = \Src\Session::get('api_token');

        if ($token) {
            (new View())->toJSON(['api_token' => $token]);
        } else {
            http_response_code(401);
            (new View())->toJSON(['error' => 'not authorized']);
        }
    }


   public function authorization(Request $request)
   {
       $login = $_POST['email'];
       $password = $_POST['password'];
       $errors = [];

       if (empty($login)) {
           $errors['login'] = 'field email cannot be blank';
       }
       if (empty($password)) {
           $errors['password'] = 'field email cannot be blank';
       }
       if (!empty($errors)) {
           http_response_code(401);
           (new View())->toJSON([
               'success' => false,
               'code' => 401,
               'message' => $errors
           ]);
           return;
       }

       $user = User::where('email', $login)->first();
       if ($user->password !== $password) {
           http_response_code(401);
           (new View())->toJSON([
               'success' => false,
               'code' => 401,
               'message' => 'Login failed'
           ]);
               return;
       }

       $token = bin2hex(random_bytes(32));
       $user->api_token = $token;
       $user->save();

       (new View())->toJSON([
           'success' => true,
           'code' => 200,
           'message' => 'Success',
           'token' => $token
       ]);

   }


    public function bearer_token(Request $request): void
    {
        (new View())->toJSON($request->all());
    }


    public function registration(Request $request)
    {
        $inputData = json_decode(file_get_contents('php://input'), true);

        if (!is_array($inputData)) {
            http_response_code(400);
            (new View())->toJSON([
                'success' => false,
                'code' => 400,
                'message' => 'Invalid input data',
            ]);
            return;
        }

        $results = [];

        foreach ($inputData as $data) {
            $errors = [];

            if (empty($data['email'])) {
                $errors['email'] = 'field email cannot be blank';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'field email must be a valid email';
            } else {
                $existingUser = User::where('email', $data['email'])->first();
                if ($existingUser) {
                    $errors['email'] = 'email already exists';
                }
            }

            if (empty($data['password'])) {
                $errors['password'] = 'field password cannot be blank';
            } elseif (strlen($data['password']) < 3) {
                $errors['password'] = 'field password must be at least 3 characters';
            } else {
                if (!preg_match('/[a-z]/', $data['password'])) {
                    $errors['password'] = 'field password must contain at one lower case letter';
                }
                if (!preg_match('/[0-9]/', $data['password'])) {
                    $errors['password'] = 'field password must contain at one number';
                }
                if (!preg_match('/[A-Z]/', $data['password'])) {
                    $errors['password'] = 'field password must contain at one upper case letter';
                }
            }

            if (empty($data['first_name'])) {
                $errors['first_name'] = 'field first name cannot be blank';
            } elseif (strlen($data['first_name']) < 2) {
                $errors['first_name'] = 'field first name must be at least 2 characters';
            }

            if (empty($data['last_name'])) {
                $errors['last_name'] = 'field last name cannot be blank';
            }

            if (!empty($errors)) {
                $results[] = [
                    'success' => false,
                    'code' => 422,
                    'message' => $errors,
                ];
                continue;
            }

            $user = new User();
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $token = bin2hex(random_bytes(32));
            $user->api_token = $token;
            $user->save();

            $results[] = [
                'success' => true,
                'code' => 200,
                'message' => 'Success',
                'token' => $token,
                'email' => $data['email'],
            ];
        }

        (new View())->toJSON($results);
    }


    public function logout(Request $request) {
        $headers = getallheaders();
        if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer token') {
            http_response_code(403);
            (new View())->toJSON([
                'message' => 'Login failed'
            ]);
            return;
        }
        $authHeader = $headers['Authorization'];
        $token = substr($authHeader, 7);
        $user = User::where('api_token', $token)->first();

        if ($user) {
            $user->api_token = null;
            $user->save();
        }
        http_response_code(204);
        exit;
    }


    public function upload_file()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        if (!isset($_FILES['file'])) {
            (new View())->toJSON(['success' => false, 'code' => 400, 'message' => 'No file uploaded']);
            return;
        }

        $uploadedFile = $_FILES['file'];
        $filename = basename($uploadedFile['name']);
        $targetDir = __DIR__ . '/../../files/';
        $targetPath = $targetDir . $filename;

        if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
            (new View())->toJSON(['success' => false, 'code' => 500, 'message' => 'Failed to save file']);
            return;
        }

        $file = new File();
        $file->name = $filename;
        $file->url = $filename;
        $file->user_id = $user->id;
        $file->save();

        (new View())->toJSON([
            'success' => true,
            'name' => $filename,
            'url' => '{{host}}/files/' . $filename,
            'file_id' => $file->id,
            'message' => 'Success'
        ]);
    }


    public function downloading_file($id)
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            (new View())->toJSON([
                'success' => false,
                'code' => 401,
                'message' => 'Login failed'
            ]);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            (new View())->toJSON([
                'success' => false,
                'code' => 401,
                'message' => 'Login failed'
            ]);
            return;
        }

        $file = File::find($id);

        if (!$file || !$file->url) {
            (new View())->toJSON([
                'success' => false,
                'code' => 404,
                'message' => 'Not found'
            ]);
            return;
        }

        $filePath = __DIR__ . '/../../files/' . $file->url;

        if (!is_file($filePath)) {
            (new View())->toJSON(['error' => 'Not found: ' . $filePath]);
            return;
        }

        if (!file_exists($filePath)) {
            (new View())->toJSON([
                'success' => false,
                'code' => 404,
                'message' => 'Not found'
            ]);
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file->url) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }


    public function rename_file($id)
    {

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            (new View())->toJSON([
                'success' => false,
                'code' => 401,
                'message' => 'Login failed'
            ]);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            (new View())->toJSON([
                'success' => false,
                'code' => 401,
                'message' => 'Login failed'
            ]);
            return;
        }

        $file = File::find($id);
        if (!$file || $file->user_id !== $user->id) {
            (new View())->toJSON([
                'success' => false,
                'code' => 404,
                'message' => 'Not found'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['name'])) {
            (new View())->toJSON([
                'success' => false,
                'code' => 400,
                'message' => 'Invalid input'
            ]);
            return;
        }

        $newName = trim($input['name']);

        $file->name = $newName;
        $file->save();

        (new View())->toJSON([
            'success' => true,
            'code' => 200,
            'message' => 'Renamed',
        ]);
    }


    public function deleting_file($id): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            (new View())->toJSON([
                "success" => false,
                "code" => 401,
                'message' => 'Login failed'
            ]);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            http_response_code(401);
            (new View())->toJSON(['error' => 'Login failed']);
            return;
        }

        $record = File::find($id);
        if (!$record) {
            http_response_code(404);
            (new View())->toJSON([
                "success" => false,
                "code" => 404,
                "message" => "Not found"
            ]);
            return;
        }

        if ($record->user_id !== $user->id) {
            http_response_code(403);
            (new View())->toJSON([
                "success" => false,
                'code' => 403,
                'message' => 'Forbidden for you'
            ]);
            return;
        }

        $filePath = __DIR__ . '/../../files/' . $record->url;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $record->delete();

        (new View())->toJSON([
            "success" => true,
            "code" => 200,
            "message" => "File deleted"
        ]);
    }


    public function disk(Request $request)
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            http_response_code(401);
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        $files = File::where('user_id', $user->id)->get();
        $result = [];
        $host = '{{host}}';

        foreach ($files as $file) {
            $accesses = [];

            $owner = User::find($file->user_id);
            $accesses[] = [
                'fullname' => trim($owner->first_name . ' ' . $owner->last_name),
                'email' => $owner->email,
                'type' => 'author'
            ];


            $result[] = [
                'file_id' => $file->id,
                'name' => $file->name,
                'code' => 200,
                'url' => $host . '/files/' . $file->url,
                'accesses' => $accesses
            ];
        }

        (new View())->toJSON($result);
    }


    public function addAccess($fileId)
    {

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        $token = trim(substr($authHeader, 7));
        $currentUser = User::where('api_token', $token)->first();

        if (!$currentUser) {
            http_response_code(401);
            (new View())->toJSON(['success' => false, 'code' => 401, 'message' => 'Login failed']);
            return;
        }

        $jsonString = file_get_contents("php://input");
        $jsonData = json_decode($jsonString, true);
        $email = $jsonData['email'] ?? null;

        if (!$email) {
            http_response_code(400);
            (new View())->toJSON(['success' => false, 'code' => 400, 'message' => 'Invalid input: email missing']);
            return;
        }

        $file = File::with('user')->find($fileId);
        if (!$file) {
            http_response_code(404);
            (new View())->toJSON(['success' => false, 'code' => 404, 'message' => 'Not found']);
            return;
        }

        if ($file->user_id !== $currentUser->id) {
            http_response_code(403);
            (new View())->toJSON(['success' => false, 'code' => 403, 'message' => 'Forbidden for you']);
            return;
        }

        $newUser = User::where('email', $email)->first();
        if (!$newUser) {
            http_response_code(404);
            (new View())->toJSON(['success' => false, 'code' => 404, 'message' => 'Not found']);
            return;
        }

        $existingAccess = Access::where('file_id', $fileId)
            ->where('user_id', $newUser->id)
            ->first();

        if ($existingAccess) {
            $body = [];

            $body[] = [
                'fullname' => trim($file->user->first_name . ' ' . $file->user->last_name),
                'email' => $file->user->email,
                'type' => 'author',
                'code' => 200
            ];

            $coAuthors = Access::with('user')->where('file_id', $fileId)->get();

            foreach ($coAuthors as $access) {
                $u = $access->user;
                $body[] = [
                    'fullname' => trim($u->first_name . ' ' . $u->last_name),
                    'email' => $u->email,
                    'type' => 'co-author',
                    'code' => 200
                ];
            }

            (new View())->toJSON([
                'message' => 'This user is already a co-author of this file',
                'data' => $body,
            ]);
            return;
        }

        Access::create([
            'file_id' => $fileId,
            'user_id' => $newUser->id,
            'type' => 'co-author',
        ]);

        $body = [];

        $body[] = [
            'fullname' => trim($file->user->first_name . ' ' . $file->user->last_name),
            'email' => $file->user->email,
            'type' => 'author',
            'code' => 200
        ];

        $coAuthors = Access::with('user')->where('file_id', $fileId)->get();

        foreach ($coAuthors as $access) {
            $u = $access->user;
            $body[] = [
                'fullname' => trim($u->first_name . ' ' . $u->last_name),
                'email' => $u->email,
                'type' => 'co-author',
                'code' => 200
            ];
        }

        (new View())->toJSON($body);
    }


    public function getSubscribers()
    {

        $headers = getallheaders();
        if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer tokkk') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $subscribers = Subscriber::with('telephone')->get();
        $result = [];

        foreach ($subscribers as $subscriber) {
            $phoneNumbers = [];

            if ($subscriber->telephone) {
                $phoneNumbers = array_map(function($tel) {
                    return $tel['phone_number'];
                }, $subscriber->telephone->toArray());
            }

            $result[] = [
                'id' => $subscriber->id,
                'name' => $subscriber->name,
                'surname' => $subscriber->surname,
                'patronymic' => $subscriber->patronymic,
                'date_of_birth' => $subscriber->date_of_birth,
                'subdivision_id' => $subscriber->subdivision_id,
                'telephones' => $phoneNumbers
            ];
        }
        (new View())->toJSON($result);
    }
}