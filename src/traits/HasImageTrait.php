<?php

namespace Kadevjo\Fibonacci\Traits;

trait HasImageTrait
{
    public function setAttribute($key, $value)
    {
        if(in_array($key,$this->getImageArray()) && $this->isBase64($value)){
            $now = new \DateTime();
            $image = $value; // your base64 encoded
            $image = str_replace('data:image/jpg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $name = $now->getTimestamp().'-'.str_random(5);
            \Storage::put('public/'.$key.'s/'.$name.'.jpg', base64_decode($image));
            parent::setAttribute($key, $key.'s/'.$name.'.jpg');
        }else
        {
            parent::setAttribute($key, $value);
        }

    }
    
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($array as $key => $attribute) {
            if (in_array($key, $this->getImageArray())) {

                if(is_null($attribute))
                {
                    $array[$key] = url("default/avatar.png");
                }
                else if (filter_var($attribute, FILTER_VALIDATE_URL)) 
                { 
                    $array[$key] = $attribute;
                }
                else
                {
                    $array[$key] = env('APP_URL').'/storage/'.$attribute;
                }
            }
        }
        return $array;
    }


    private function getImageArray(){
        return ($this->images) ? $this->images: ['image'];
    }

    private function isBase64($string){
        $decoded = base64_decode($string, true);

        // Check if there is no invalid character in string
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;

        // Decode the string in strict mode and send the response
        if (!base64_decode($string, true)) return false;

        // Encode and compare it to original one
        if (base64_encode($decoded) != $string) return false;

        return true;
    }
}
