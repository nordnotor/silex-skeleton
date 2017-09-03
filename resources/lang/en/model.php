<?php

return [
    'user' => [
        'creating' => 'User',
        'modelName' => 'User',
        'email' => 'Email',
        'first_name' => 'First name',
        'middle_name' => 'Middle name',
        'last_name' => 'Last name',
        'role' => 'Role	',
        'password' => 'Password',
        'password_confirmation' => 'Password confirmation',
        'birthday' => 'Birthday',
        'sex' => 'Sex',
        'phone' => 'Phone number',
        'specializations' => 'Specializations',
        'duration' => 'Duration',
        'works' => 'Works',
        'admin' => 'Is Admin',
        'type' => 'Type',
        'status	' => 'Status',
        'logged_at	' => 'Last login at.',
        'country' => 'Country',
        'region' => 'Region',
        'city' => 'City',
        'area' => 'Area',
        'street' => 'Street',
        'house' => 'House',
        'apartment' => 'Apartment',
        'description' => 'Description',
        'category' => 'Category',
        'doctor_type' => 'Doctor is',
        'experience' => 'Experience',
        'degree' => 'Degree',
        'status' => 'Status',
        'logged_at' => 'Last logged at',
    ],

    'clinic' => [
        'modelName' => 'Clinic',
        'creating' => 'Clinic',

        'name' => 'Name',
        'city_id' => 'City',
        'region' => 'Region',
        'metro' => 'Metro',
        'address' => 'Address',
        'street' => 'Street',
        'house' => 'House',
        'contact' => 'Contact',
        'description_little' => 'Little description',
        'description' => 'Description',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'location' => 'Location',
        'status' => 'Status',
        'user_id' => 'User',
        'clinic_type_id' => 'Clinic type',
        'organization_id' => 'Organization',
        'own_type' => 'Type',
        'phone' => 'Phone',
    ],
    'clinic_type' => [
        'modelName' => 'Clinic type',
        'creating' => 'Clinic type',

        'name' => 'Name',
    ],
    'organization' => [
        'modelName' => 'Organization',
        'creating' => 'Organization',

        'name' => 'Name',
        'user_id' => 'User',
    ],
    'specialization' => [
        'modelName' => 'Specialization',
        'creating' => 'Specialization',

        'name' => 'Name',
        'code' => 'Code',
    ],
    'role' => [
        'modelName' => 'Role',
        'creating' => 'Role',

        'name' => 'Name',
        'type' => 'Type',
        'default' => 'Is default'
    ],


    'worker' => [
        'modelName' => 'Worker',
        'creating' => 'Worker',

        'user_id' => 'User',
        'clinic_id' => 'Clinic',
        'role_id' => 'Role',
        'can' => 'Can',
        'type' => 'Type',
        'status' => 'Status',
        'time' => 'Default visit time',
        'position' => 'Position',
        'schedule' => 'Schedule',

        'data_expire' => 'Expire data',
        'blocked' => 'Blocked',
        'user_parent_id' => 'User parent'
    ],


    'city' => [
        'modelName' => 'City',
        'creating' => 'City',

        'name' => 'Name',
        'region_id' => 'Region',
    ],

    'region' => [
        'modelName' => 'Region',
        'creating' => 'Region',

        'name' => 'Name',
        'country_id' => 'Country',
    ],
    'country' => [
        'modelName' => 'Country',
        'creating' => 'Country',

        'name' => 'Name',
    ],

    'comment' => [
        'modelName' => 'Comment',
        'creating' => 'Comment',

        'title' => 'Title',
        'text' => 'Text',
        'mark' => 'Mark',
        'status' => 'Status',
        'type' => 'Type',
        'obj_id' => 'Object',
        'obj_type' => 'Type of object',
        'parent_id' => 'Parent comment',
    ],
];