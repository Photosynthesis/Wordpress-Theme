module Directory.Communities exposing (..)

{-| Contains Community Data Types & Related Functions
-}

import Date exposing (Date)


{-| A Communities Unique ID is an Integer
-}
type CommunityID
    = CommunityID Int


{-| The Forming Status of a Community.
-}
type CommunityStatus
    = Forming
    | Established
    | Reforming
    | Disbanded


{-| The Visitor Policy of a Community.
-}
type VisitorsWelcome
    = Welcome
    | Rarely
    | NoVisitors


{-| The Membership Application Status of a Community
-}
type MembersWelcome
    = Yes
    | Waitlist
    | NoMembers


{-| The Various Types of Communities We List.
-}
type CommunityType
    = Commune
    | EcoVillage
    | CoHousing
    | SharedHousing
    | StudentHousing
    | Spiritual
    | Other
    | TransitionTown
    | Indigenous


{-| The Data Associated with a Specific Community.
-}
type alias Community =
    { id : CommunityID
    , name : String
    , slug : String
    , imageUrl : Maybe String
    , thumbnailUrl : Maybe String
    , status : CommunityStatus
    , city : String
    , state : String
    , country : String
    , openToVisitors : VisitorsWelcome
    , openToMembers : MembersWelcome
    , communityTypes : List CommunityType
    , updatedAt : Date
    , createdAt : Date
    }


{-| Render a `CommunityStatus`.
-}
statusToString : CommunityStatus -> String
statusToString status =
    case status of
        Established ->
            "Established (4+ adults, 2+ years)"

        Forming ->
            "Forming"

        Reforming ->
            "Re-Forming"

        Disbanded ->
            "Disbanded"


{-| Render a `VisitorsWelcome` Status.
-}
visitorsWelcomeToString : VisitorsWelcome -> String
visitorsWelcomeToString welcomeStatus =
    case welcomeStatus of
        Welcome ->
            "Yes"

        Rarely ->
            "Yes, rarely"

        NoVisitors ->
            "No"


{-| Render a `MembersWelcome` Status.
-}
membersWelcomeToString : MembersWelcome -> String
membersWelcomeToString welcomeStatus =
    case welcomeStatus of
        Yes ->
            "Yes"

        Waitlist ->
            "Not currently, but there is a list or possibly in the future"

        NoMembers ->
            "No"


{-| Render a `CommunityType`.
TODO: make a typeToHtml with parentheticals as abbr tags
-}
typeToString : CommunityType -> String
typeToString communityType =
    case communityType of
        Commune ->
            "Commune (organized around sharing almost everything)"

        EcoVillage ->
            "Ecovillage (organized around ecology and sustainability)"

        CoHousing ->
            "Cohousing (individual homes within group property)"

        SharedHousing ->
            "Shared Housing, Cohouseholding, or Coliving (multiple individuals sharing a dwelling)"

        StudentHousing ->
            "Student Housing or Student Co-Op"

        Spiritual ->
            "Spiritual or Religious"

        Other ->
            "Unspecified or Other"

        TransitionTown ->
            "Transition Town or Eco-Neighborhood (focused on energy/resource resiliency)"

        Indigenous ->
            "Traditional or Indigenous"
