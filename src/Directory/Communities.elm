module Directory.Communities exposing (..)

{-| Contains Community Data Types & Related Functions
-}

import Date exposing (Date)
import Map exposing (Coords)


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


{-| Additional Information about the Communitiy's Status.
-}
type alias ExtraStatusInfo =
    { year : String
    , info : String
    }


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


{-| Public Street Addresses of Communities.
-}
type alias PublicAddress =
    { lineOne : String
    , lineTwo : String
    , zipCode : String
    , addressType : PublicAddressType
    }


{-| The Type of Address We Have for a Community.
-}
type PublicAddressType
    = CommunityAddress
    | MailingAddress


{-| The Type of Area the Community is Located In.
-}
type LocationType
    = Rural
    | Urban
    | Suburban
    | SmallTown
    | Mobile
    | LocationTBD


{-| The Development Status of the Community's Land.
-}
type LandStatus
    = NoLand
    | UndevelopedLand
    | PermittingLand
    | DevelopedLand


{-| The Type of Income Sharing Community Members Participate In.
-}
type IncomeSharing
    = NoIncomeSharing
    | FullIncomeSharing
    | PartialIncomeSharing


{-| The Building Status for a Cohousing Community.
-}
type CohousingStatus
    = CohousingBuilding
    | CohousingCompleted
    | CohousingDisbanded
    | CohousingForming
    | CohousingOwnSite
    | CohousingRetrofitting
    | CohousingSeekingSite
    | CohousingSiteOptioned
    | CohousingUnknown


{-| Additional Details for Cohousing Communities.
-}
type alias CohousingData =
    { siteStatus : Maybe CohousingStatus
    , yearCompleted : Maybe Int
    , housingUnits : Maybe Int
    , hasSharedBuilding : Maybe Bool
    , sharedBuildingArea : Maybe Int
    , architect : String
    , developer : String
    , lender : String
    }


type alias ImageData =
    { thumbnailUrl : Maybe String
    , imageUrl : String
    }


{-| The Data Associated with a Specific Community for the Listings pages.
-}
type alias CommunityListing =
    { id : CommunityID
    , name : String
    , slug : String
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


{-| The Data Associated with a Specific Community for the Details pages.
-}
type alias CommunityDetails =
    { id : CommunityID
    , name : String
    , slug : String
    , image : Maybe ImageData
    , missionStatement : String
    , description : String
    , status : CommunityStatus
    , disbandedInfo : Maybe ExtraStatusInfo
    , reformingInfo : Maybe ExtraStatusInfo
    , startedPlanning : Int
    , startedLivingTogether : Int
    , contactName : String
    , contactPhone : String
    , contactAddress : Maybe PublicAddress
    , city : String
    , state : String
    , country : String
    , websiteUrl : String
    , businessUrl : String
    , facebookUrl : String
    , twitterUrl : String
    , socialUrl : String
    , openToVisitors : VisitorsWelcome
    , openToMembers : MembersWelcome
    , isFicMember : Bool
    , ficMembershipStartYear : String
    , mapCoordinates : Maybe Coords
    , communityTypes : List CommunityType
    , programsAndActivites : List String
    , location : LocationType
    , landStatus : Maybe LandStatus
    , landSizeAmount : Maybe Float
    , landSizeUnits : Maybe String
    , currentResidenceTypes : List String
    , currentResidences : Maybe Int
    , plannedResidenceTypes : List String
    , plannedResidences : Maybe Int
    , housingAccess : List String
    , landOwner : Maybe String
    , housingComments : Maybe String
    , adultCount : Int
    , childCount : Maybe Int
    , nonmemberCount : Maybe Int
    , percentMale : Maybe String
    , percentFemale : Maybe String
    , percentTrans : Maybe String
    , visitorProcess : Maybe String
    , membershipProcess : Maybe String
    , membershipComments : Maybe String
    , decisionMaking : String
    , leader : String
    , leadershipGroup : Maybe String
    , governmentComments : Maybe String
    , hasJoinFee : Bool
    , joinFee : Maybe Float
    , hasRegularFees : Bool
    , regularFees : Maybe Float
    , sharedIncome : IncomeSharing
    , contributeLabor : String
    , laborHours : Maybe Float
    , memberDebt : Maybe String
    , economicsComments : Maybe String
    , energyInfrastructure : Maybe String
    , currentRenewablePercentage : Maybe String
    , renewableSources : Maybe (List String)
    , plannedRenewablePercentage : Maybe String
    , currentFoodPercentage : Maybe String
    , plannedFoodPercentage : Maybe String
    , localFoodPercentage : Maybe String
    , facilities : List String
    , internetAccess : Maybe String
    , internetSpeed : Maybe String
    , cellService : Maybe String
    , sharedMeals : Maybe String
    , dietaryPractices : List String
    , commonDiet : Maybe String
    , specialDiets : Maybe String
    , alcohol : Maybe String
    , tobacco : Maybe String
    , dietComments : Maybe String
    , spiritualPractices : List String
    , religionExpected : Maybe String
    , education : List String
    , commonHealthcarePractice : Maybe String
    , healthcareComments : Maybe String
    , healthcareOptions : List String
    , lifestyleComments : Maybe String
    , cohousing : Maybe CohousingData
    , additionalComments : Maybe String
    , galleryImages : List ImageData
    , youtubeIds : List String
    , networkAffiliations : List String
    , otherAffiliations : String
    , communityAffiliations : Maybe String
    , fairHousingComplaint : Bool
    , keywords : String
    , updatedAt : Date
    , createdAt : Date
    , isAdmin : Bool
    , isOwner : Bool
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


{-| Render a `PublicAddressType`
-}
addressTypeToString : PublicAddressType -> String
addressTypeToString addressType =
    case addressType of
        CommunityAddress ->
            "Community Address"

        MailingAddress ->
            "Mailing Address"


{-| Render a `LocationType`
-}
locationTypeToString : LocationType -> String
locationTypeToString locationType =
    case locationType of
        Rural ->
            "Rural"

        Urban ->
            "Urban"

        Suburban ->
            "Suburban"

        SmallTown ->
            "Small Town or Village"

        Mobile ->
            "Mobile"

        LocationTBD ->
            "To Be Determined"


landStatusToString : LandStatus -> String
landStatusToString status =
    case status of
        NoLand ->
            "We do not have land"

        UndevelopedLand ->
            "We have undeveloped land"

        PermittingLand ->
            "We have land in the permitting or zoning stage"

        DevelopedLand ->
            "We have land we have developed on"


incomeSharingToString : IncomeSharing -> String
incomeSharingToString sharing =
    case sharing of
        NoIncomeSharing ->
            "None"

        FullIncomeSharing ->
            "All or Close to All"

        PartialIncomeSharing ->
            "Partial Sharing of Income"


cohousingStatusToString : CohousingStatus -> String
cohousingStatusToString status =
    case status of
        CohousingBuilding ->
            "Building"

        CohousingCompleted ->
            "Completed"

        CohousingDisbanded ->
            "Disbanded"

        CohousingForming ->
            "Forming"

        CohousingOwnSite ->
            "Own Site"

        CohousingRetrofitting ->
            "Retrofitting"

        CohousingSeekingSite ->
            "Seeking Site"

        CohousingSiteOptioned ->
            "Site Optioned"

        CohousingUnknown ->
            "Unknown"
