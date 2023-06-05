<?php
namespace SarCubet\FileUpload\Features;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidateObj;
use InvalidArgumentException;
use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;

class FileValidate extends Upload
{
    public function __construct()
    {
        
    }

    public function validateFile(UploadedFile $file, array $rulesAndMessagesArray = []) : ValidateObj
    {
        $rulesArray = array_keys($rulesAndMessagesArray);
        $messagesArray = array_values($rulesAndMessagesArray);

        $rules = [];
        $messages = [];

        if (count($rulesArray)) {
            if (count($messagesArray)) {
                if (!$this->checkRulesAndMessagesCount($rulesArray, $messagesArray)) {
                    throw new InvalidArgumentException("Number of rules doesn't match number of messages.");
                }
                foreach ($rulesArray as $key => $rule) {
                    if (strpos($rule, ':') !== false) {
                        $rule = explode(':', $rule)[0];
                    }
                    $messages['file.' . $rule] = $messagesArray[$key];
                }
            }

            $rules = [
                'file' => implode('|', $rulesArray)
            ];
        } else {
            $allowedImageExtensions = config('fileUpload.allowed_file_extensions.image');
            $allowedDocumentExtensions = config('fileUpload.allowed_file_extensions.doc');
            $allowedTextExtensions = config('fileUpload.allowed_file_extensions.text');
            $allowedOtherExtensions = config('fileUpload.allowed_file_extensions.others');
            $sizeLimit = config('fileUpload.default_file_size_limit');
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, $allowedImageExtensions)) {
                $mimes = implode(',', $allowedImageExtensions);
                $rules = [
                    'file' => 'required|image|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedDocumentExtensions)) {
                $mimes = implode(',', $allowedDocumentExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedTextExtensions)) {
                $mimes = implode(',', $allowedTextExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedOtherExtensions)) {
                $mimes = implode(',', $allowedOtherExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } else {
                $mimes = '';
                if (count($allowedImageExtensions)) {
                    $mimes .= implode(',', $allowedImageExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedDocumentExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedDocumentExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedTextExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedTextExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedOtherExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedOtherExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            }
        }

        $validator = $this->validate($file, $rules, $messages);
        return $validator;
    }

    private function checkRulesAndMessagesCount($rules, $messages)
    {
        return count($rules) === count($messages);
    }

    private function trimChar($char, $string)
    {
        $string = ltrim($string, $char);
        $string = rtrim($string, $char);
        return $string;
    }

    private function validate($file, $rules, $messages)
    {
        $validator = Validator::make(['file' => $file], $rules, $messages);
        return $validator;
    }
}
