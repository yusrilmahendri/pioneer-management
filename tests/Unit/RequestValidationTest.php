<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Requests\Business\UpdateBusinessRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\StoreBusinessCategoryRequest;
use App\Http\Requests\StoreProductCategoryRequest;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test StoreUserRequest validation rules
     */
    public function test_store_user_request_validation(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'username' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee',
            'salary' => 5000000
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test invalid name (too short)
        $invalidData = $validData;
        $invalidData['name'] = 'A';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test invalid email format
        $invalidData = $validData;
        $invalidData['email'] = 'invalid-email';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        // Test invalid phone format
        $invalidData = $validData;
        $invalidData['phone'] = '123'; // Invalid format
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());

        // Test password mismatch
        $invalidData = $validData;
        $invalidData['password_confirmation'] = 'DifferentPassword123!';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        // Test invalid gender
        $invalidData = $validData;
        $invalidData['gender'] = 'other';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());

        // Test invalid account role
        $invalidData = $validData;
        $invalidData['account_role'] = 'invalid_role';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('account_role', $validator->errors()->toArray());

        // Test invalid salary (too low)
        $invalidData = $validData;
        $invalidData['salary'] = 1000000;
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('salary', $validator->errors()->toArray());
    }

    /**
     * Test StoreBusinessCategoryRequest validation
     */
    public function test_store_business_category_validation(): void
    {
        $request = new StoreBusinessCategoryRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'name_category_business' => 'Food & Beverage'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test missing required field
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name_category_business', $validator->errors()->toArray());

        // Test empty string
        $invalidData = ['name_category_business' => ''];
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name_category_business', $validator->errors()->toArray());
    }

    /**
     * Test StoreProductCategoryRequest validation
     */
    public function test_store_product_category_validation(): void
    {
        $request = new StoreProductCategoryRequest();
        $rules = $request->rules();

        // Test valid data
        $validData = [
            'name_category_product' => 'Beverages'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Test missing required field
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name_category_product', $validator->errors()->toArray());

        // Test empty string
        $invalidData = ['name_category_product' => ''];
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name_category_product', $validator->errors()->toArray());
    }

    /**
     * Test phone number validation formats
     */
    public function test_phone_number_validation_formats(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $baseData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'username' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee',
            'salary' => 5000000
        ];

        // Valid Indonesian phone formats
        $validPhones = [
            '081234567890',
            '081234567890123', // 13 digits
            '6281234567890',
            '+6281234567890',
            '08123456789' // 9 digits after 0
        ];

        foreach ($validPhones as $phone) {
            $data = $baseData;
            $data['phone'] = $phone;
            $data['email'] = "test{$phone}@test.com"; // Make email unique
            $data['username'] = "user{$phone}"; // Make username unique
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Phone {$phone} should be valid");
        }

        // Invalid phone formats
        $invalidPhones = [
            '12345678',      // Too short
            '0812345678901234', // Too long
            '021234567890',  // Landline format
            '881234567890',  // Invalid operator
            '+6211234567890' // Landline with country code
        ];

        foreach ($invalidPhones as $phone) {
            $data = $baseData;
            $data['phone'] = $phone;
            $data['email'] = "test{$phone}@test.com";
            $data['username'] = "user" . str_replace('+', '', $phone);
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->fails(), "Phone {$phone} should be invalid");
        }
    }

    /**
     * Test password complexity validation
     */
    public function test_password_complexity_validation(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $baseData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'username' => 'johndoe',
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee',
            'salary' => 5000000
        ];

        // Valid passwords
        $validPasswords = [
            'Password123!',
            'MyStr0ng@Pass',
            'C0mplex#Pass123'
        ];

        foreach ($validPasswords as $password) {
            $data = $baseData;
            $data['password'] = $password;
            $data['password_confirmation'] = $password;
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Password {$password} should be valid");
        }

        // Invalid passwords
        $invalidPasswords = [
            'password',       // No uppercase, numbers, symbols
            'PASSWORD',       // No lowercase, numbers, symbols  
            'Password',       // No numbers, symbols
            'Password123',    // No symbols
            'Pass123!',       // Too short (7 chars)
            '12345678'        // No letters
        ];

        foreach ($invalidPasswords as $password) {
            $data = $baseData;
            $data['password'] = $password;
            $data['password_confirmation'] = $password;
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->fails(), "Password {$password} should be invalid");
        }
    }

    /**
     * Test date validation
     */
    public function test_date_validation(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $baseData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'username' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '081234567890',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee',
            'salary' => 5000000
        ];

        // Test valid dates
        $data = $baseData;
        $data['birth_of_date'] = '1990-01-01';
        $data['start_date'] = '2023-01-01';
        
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());

        // Test birth date in future (invalid)
        $data = $baseData;
        $data['birth_of_date'] = '2030-01-01';
        $data['start_date'] = '2023-01-01';
        
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('birth_of_date', $validator->errors()->toArray());

        // Test start date too early (invalid)
        $data = $baseData;
        $data['birth_of_date'] = '1990-01-01';
        $data['start_date'] = '2019-01-01';
        
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());

        // Test end date before start date (invalid)
        $data = $baseData;
        $data['birth_of_date'] = '1990-01-01';
        $data['start_date'] = '2023-01-01';
        $data['end_date'] = '2022-01-01';
        
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }

    /**
     * Test username validation
     */
    public function test_username_validation(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $baseData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee',
            'salary' => 5000000
        ];

        // Valid usernames
        $validUsernames = [
            'johndoe',
            'john_doe',
            'john-doe',
            'john123',
            'user_123'
        ];

        foreach ($validUsernames as $username) {
            $data = $baseData;
            $data['username'] = $username;
            $data['email'] = "test{$username}@test.com";
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Username {$username} should be valid");
        }

        // Invalid usernames
        $invalidUsernames = [
            'jo',           // Too short
            'john.doe',     // Contains dot
            'john doe',     // Contains space
            'john@doe',     // Contains @
            'john#doe'      // Contains #
        ];

        foreach ($invalidUsernames as $username) {
            $data = $baseData;
            $data['username'] = $username;
            $data['email'] = "test" . str_replace(['@', '#', '.', ' '], '', $username) . "@test.com";
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->fails(), "Username {$username} should be invalid");
        }
    }

    /**
     * Test salary validation
     */
    public function test_salary_validation(): void
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();
        
        $baseData = [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'username' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'account_role' => 'employee'
        ];

        // Valid salaries
        $validSalaries = [2000000, 5000000, 10000000, 50000000, 100000000];

        foreach ($validSalaries as $salary) {
            $data = $baseData;
            $data['salary'] = $salary;
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->passes(), "Salary {$salary} should be valid");
        }

        // Invalid salaries
        $invalidSalaries = [
            1999999,    // Below minimum
            100000001,  // Above maximum
            -1000000,   // Negative
            'invalid'   // Non-numeric
        ];

        foreach ($invalidSalaries as $salary) {
            $data = $baseData;
            $data['salary'] = $salary;
            
            $validator = Validator::make($data, $rules);
            $this->assertTrue($validator->fails(), "Salary {$salary} should be invalid");
        }
    }
}
