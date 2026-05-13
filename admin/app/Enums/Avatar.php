<?php declare(strict_types=1);

namespace App\Enums;

/**
 * Avatar enum
 *
 * @package  App
 * @category Enums
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum Avatar: string {
    case ElectricalWorker = "electrical-worker.svg";
    case Mechanic = "mechanic.svg";
    case Specialist = "specialist.svg";
    case User = "user.svg";
    case UserAlt = "user-alt.svg";
    case UserAvatar = "user-avatar.svg";
    case UserAvatarFilled = "user-avatar-filled.svg";
    case UserAvatarFilledAlt = "user-avatar-filled-alt.svg";
    case UserAvatarProfile = "user-avatar-profile.svg";
    case UserSilhouette = "user-silhouette.svg";
    case UserSocial = "user-social.svg";
    case UserSocialAlt = "user-social-alt.svg";
}
