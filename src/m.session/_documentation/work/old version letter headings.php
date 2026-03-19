		// Old version
		// $lastLetter = '';
		// foreach ($users as $user)
		// {
			// $firstLetter = strtoupper(substr($user['username'], 0, 1));
			
			// // If the letter changed, print a new heading
			// if ($firstLetter !== $lastLetter)
			// {
				// $html .= '<div class="user-letter">' . $firstLetter . '</div>';
				// $lastLetter = $firstLetter;
			// }
			
			// $html .= '<div class="user-item"><a style="' . $user['style'] . '" href="';
			// if($user['isreal']) $html .= 'https://development.listiary.org/session/m.user.php?id=' . $user['id'] . '">';
			// else $html .= 'javascript:void(0);">';
			// $html .= ($user['star'] ? '✦ ' : '') . htmlspecialchars($user['username']) . '</a></div>';
		// }