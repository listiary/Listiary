<?php

	// Session Module Core
	class TableAccounts {

		public static $tableName = 'accounts';

		public static $id = 'id';
		public static $userName = 'username';
		public static $email = 'email';
		public static $userCode = 'usercode';
		public static $passwordHash = 'password_hash';
		public static $isBot = 'is_bot';
		public static $isActive = 'is_active';
		public static $isPremium = 'is_premium';
		public static $createdAt = 'created_at';
		public static $verificationToken = 'verification_token';
	}
	
	class TableAccountDetails {

		public static $tableName = 'account_details';
		
		//foreign key
		public static $accountId = 'account_id';
		
		//avatar
		public static $avatarPath = 'avatar_path';
		public static $avatarShape = 'avatar_shape';
		public static $avatarShapeRadius = 'avatar_shape_radius';
		public static $avatarUpdatedAt = 'avatar_updated_at';
		
		//bio & location
		public static $bio = 'bio';
		public static $city = 'city';
		public static $country = 'country';
		public static $timezone = 'timezone';
		
		//social
		public static $linkPersonalWebsite = 'link_personal_website';
		public static $linkPersonalFacebook = 'link_personal_facebook';
		public static $linkPersonalXcom = 'link_personal_xcom';
		public static $linkPersonalLinkedin = 'link_personal_linkedin';
		public static $linkPersonalOther = 'link_personal_other';
		
		//optional contact phone
		public static $phoneMain = 'phone1';
		public static $isPhoneMainVerified = 'phone1_verified';
		public static $phoneSecondary = 'phone2';
		public static $isPhoneSecondaryVerified = 'phone2_verified';
	}
	
	class TablePasswordResets {

		public static $tableName = 'password_resets';
		
		public static $email = 'email';
		public static $token = 'token';
		public static $expiresAt = 'expires_at';
	}
	
	class TablePersistentLogins {
		
		public static $tableName = 'persistent_logins';
		
		public static $id = 'id';
		public static $userId = 'user_id';
		
		public static $selector = 'selector';
		public static $tokenHash = 'token_hash';
		public static $createdAt = 'created_at';
		public static $expiresAt = 'expires_at';
	}


	// Session Module - Rate Limiters 
	class TableLoginAttempts {
		
		public static $tableName = 'login_attempts';
		
		public static $id = 'id';
		public static $email = 'email';
		public static $ipAddress = 'ip_address';
		public static $attemptTime = 'attempt_time';
	}
	
	class TableRegisterSuccess {
		
		public static $tableName = 'register_success';
		
		public static $id = 'id';
		public static $email = 'email';
		public static $ipAddress = 'ip_address';
		public static $attemptTime = 'attempt_time';
	}
	
	class TablePasswordResetResends {
		
		public static $tableName = 'password_reset_resends';
		
		public static $id = 'id';
		public static $email = 'email';
		public static $ipAddress = 'ip_address';
		public static $sendTime = 'send_time';
	}
