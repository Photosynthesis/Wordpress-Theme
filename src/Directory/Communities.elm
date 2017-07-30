module Communities exposing (..)

import Date exposing (Date)


-- Community Model


type CommunityID
    = CommunityID Int


type CommunityStatus
    = Forming
    | Established
    | Reforming
    | Disbanded


type VisitorsWelcome
    = Welcome
    | Rarely
    | NoVisitors


type MembersWelcome
    = Yes
    | Waitlist
    | NoMembers


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


visitorsWelcomeToString : VisitorsWelcome -> String
visitorsWelcomeToString welcomeStatus =
    case welcomeStatus of
        Welcome ->
            "Yes"

        Rarely ->
            "Yes, rarely"

        NoVisitors ->
            "No"


membersWelcomeToString : MembersWelcome -> String
membersWelcomeToString welcomeStatus =
    case welcomeStatus of
        Yes ->
            "Yes"

        Waitlist ->
            "Not currently, but there is a list or possibly in the future"

        NoMembers ->
            "No"



-- TODO: make a typeToHtml with parentheticals as abbr tags


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
