module Directory.Decoders exposing (communityDetails, communityListing)

import Date exposing (Date)
import Json.Decode as Decode exposing (Decoder, string, int, bool)
import Json.Decode.Pipeline exposing (decode, required, optional)
import Directory.Communities exposing (..)
import Map exposing (Coords)


communityDetails : Decoder CommunityDetails
communityDetails =
    decode CommunityDetails
        |> required "id" communityID
        |> required "name" string
        |> required "slug" string
        |> maybe "image" imageData
        |> required "missionStatement" string
        |> required "description" string
        |> required "communityStatus" communityStatus
        |> optional "disbandedData" (Decode.map Just extraStatusInfo) Nothing
        |> optional "reformingData" (Decode.map Just extraStatusInfo) Nothing
        |> required "startedPlanning" int
        |> required "startedLivingTogether" int
        |> optional "contactName" string ""
        |> optional "contactPhone" string ""
        |> optional "contactAddress" (Decode.map Just publicAddress) Nothing
        |> optional "city" string ""
        |> optional "state" string ""
        |> optional "country" string ""
        |> optional "websiteUrl" string ""
        |> optional "businessUrl" string ""
        |> optional "facebookUrl" string ""
        |> optional "twitterUrl" string ""
        |> optional "socialUrl" string ""
        |> required "openToVisitors" visitorsWelcome
        |> required "openToMembership" membersWelcome
        |> required "isFicMember" bool
        |> optional "ficMembershipStart" string ""
        |> maybe "mapCoordinates" coords
        |> required "communityTypes" (oneOrList communityType)
        |> required "programs" (Decode.list string)
        |> required "location" locationType
        |> optional "landStatus" (Decode.map Just landStatus) Nothing
        |> optional "landSizeAmount" (Decode.map Just Decode.float) Nothing
        |> optional "landSizeUnits" (Decode.map Just Decode.string) Nothing
        |> required "currentResidenceTypes" (Decode.list Decode.string)
        |> maybe "currentResidences" Decode.int
        |> required "plannedResidenceTypes" (Decode.list Decode.string)
        |> maybe "plannedResidences" Decode.int
        |> required "housingAccess" (oneOrList Decode.string)
        |> maybe "landOwner" Decode.string
        |> maybe "housingComments" Decode.string
        |> required "adultCount" Decode.int
        |> maybe "childCount" Decode.int
        |> maybe "nonmemberCount" Decode.int
        |> maybe "percentMale" Decode.string
        |> maybe "percentFemale" Decode.string
        |> maybe "percentTrans" Decode.string
        |> maybe "visitorProcess" Decode.string
        |> maybe "membershipProcess" Decode.string
        |> maybe "membershipComments" Decode.string
        |> required "decisionMaking" Decode.string
        |> required "leader" Decode.string
        |> maybe "leadershipGroup" Decode.string
        |> maybe "governmentComments" Decode.string
        |> required "hasJoinFee" Decode.bool
        |> maybe "joinFee" Decode.float
        |> required "hasRegularFees" Decode.bool
        |> maybe "regularFees" Decode.float
        |> required "sharedIncome" incomeSharing
        |> required "contributeLabor" Decode.string
        |> maybe "laborHours" Decode.float
        |> maybe "memberDebt" Decode.string
        |> maybe "economicsComments" Decode.string
        |> maybe "energyInfrastructure" Decode.string
        |> maybe "renewablePercentage" Decode.string
        |> maybe "renewableSources" (Decode.list Decode.string)
        |> maybe "plannedRenewablePercentage" Decode.string
        |> maybe "currentFoodPercentage" Decode.string
        |> maybe "plannedFoodPercentage" Decode.string
        |> maybe "localFoodPercentage" Decode.string
        |> required "facilities" (Decode.list Decode.string)
        |> maybe "internetAccess" Decode.string
        |> maybe "internetSpeed" Decode.string
        |> maybe "cellService" Decode.string
        |> maybe "sharedMeals" Decode.string
        |> required "dietaryPractices" (Decode.list Decode.string)
        |> maybe "commonDiet" Decode.string
        |> maybe "specialDiets" Decode.string
        |> maybe "alcohol" Decode.string
        |> maybe "tobacco" Decode.string
        |> maybe "dietComments" Decode.string
        |> required "spiritualPractices" (Decode.list Decode.string)
        |> maybe "religionExpected" Decode.string
        |> required "education" (oneOrList Decode.string)
        |> maybe "commonHealthcarePractice" Decode.string
        |> maybe "healthcareComments" Decode.string
        |> required "healthcareOptions" (oneOrList Decode.string)
        |> maybe "lifestyleComments" Decode.string
        |> maybe "cohousing" cohousingData
        |> maybe "additionalComments" Decode.string
        |> optional "galleryImages" (Decode.list imageData) []
        |> optional "youtubeIds" (Decode.list string) []
        |> optional "networkAffiliations" (Decode.list string) []
        |> optional "otherAffiliations" string ""
        |> maybe "communityAffiliations" Decode.string
        |> required "fairHousingComplaint" Decode.bool
        |> optional "keywords" string ""
        |> required "updatedAt" date
        |> required "createdAt" date
        |> required "isAdmin" Decode.bool
        |> required "isOwner" Decode.bool


{-| Decode an optional field into a Maybe value.
-}
maybe : String -> Decoder a -> Decoder (Maybe a -> b) -> Decoder b
maybe key decoder =
    optional key (Decode.map Just decoder) Nothing


communityListing : Decoder CommunityListing
communityListing =
    decode CommunityListing
        |> required "id" communityID
        |> required "name" string
        |> required "slug" string
        |> required "thumbnailUrl" (Decode.nullable string)
        |> required "communityStatus" communityStatus
        |> optional "city" string ""
        |> optional "state" string ""
        |> optional "country" string ""
        |> required "openToVisitors" visitorsWelcome
        |> required "openToMembership" membersWelcome
        |> required "communityTypes" (oneOrList communityType)
        |> required "updatedAt" date
        |> required "createdAt" date


communityID : Decoder CommunityID
communityID =
    Decode.map CommunityID Decode.int


communityStatus : Decoder CommunityStatus
communityStatus =
    let
        decoder str =
            if String.contains "established" str then
                Ok Established
            else if String.contains "re-forming" str then
                Ok Reforming
            else if String.contains "forming" str then
                Ok Forming
            else if String.contains "disbanded" str then
                Ok Disbanded
            else
                Err <| "Could not Decode " ++ str
    in
        Decode.string
            |> Decode.andThen (String.toLower >> decoder >> fromResult)


extraStatusInfo : Decoder ExtraStatusInfo
extraStatusInfo =
    decode ExtraStatusInfo
        |> optional "year" string ""
        |> optional "info" string ""


visitorsWelcome : Decoder VisitorsWelcome
visitorsWelcome =
    let
        decoder str =
            if str == "yes" then
                Ok Welcome
            else if String.contains "rarely" str then
                Ok Rarely
            else if str == "no" then
                Ok NoVisitors
            else
                Err <| "Could not Decode " ++ str
    in
        Decode.string
            |> Decode.andThen (String.toLower >> decoder >> fromResult)


membersWelcome : Decoder MembersWelcome
membersWelcome =
    let
        decoder str =
            if str == "yes" then
                Ok Yes
            else if str == "no" then
                Ok NoMembers
            else if String.contains "not currently" str then
                Ok Waitlist
            else
                Err <| "Could not Decode " ++ str
    in
        Decode.string
            |> Decode.andThen (String.toLower >> decoder >> fromResult)


communityType : Decoder CommunityType
communityType =
    stringToEnum
        [ ( CoHousing, "cohousing (individual homes within group owned property.)" )
        , ( Commune, "commune (organized around sharing almost everything.)" )
        , ( EcoVillage, "ecovillage (organized around ecology and sustainability.)" )
        , ( Indigenous, "traditional or indigenous community" )
        , ( Other, "ethical business~ investment group~ or alternative currency" )
        , ( Other, "land trust" )
        , ( Other, "neighborhood or community housing association" )
        , ( Other, "neighborhood, community housing, or homeowner\\'s association" )
        , ( Other, "organizations~ resources~ or networks" )
        , ( Other, "other" )
        , ( Other, "school~ educational institute or experience" )
        , ( Other, "unspecified, or other" )
        , ( Other, "volunteer~ internship~ apprenticeship~ or wwoof’ing" )
        , ( SharedHousing, "shared housing (multiple individuals sharing a dwelling.)" )
        , ( SharedHousing, "shared housing or co-living (multiple individuals sharing a dwelling.)" )
        , ( SharedHousing, "shared housing, cohouseholding, or coliving (multiple individuals sharing a dwelling.)" )
        , ( Spiritual, "spiritual or religious community or organization" )
        , ( Spiritual, "spiritual or religious community" )
        , ( StudentHousing, "student housing or student co-op" )
        , ( TransitionTown, "transition town (post-petroleum and off-grid communities.)" )
        , ( TransitionTown, "transition town or eco-neighborhood (focused on energy/resource resiliency)" )
        ]


publicAddress : Decoder PublicAddress
publicAddress =
    decode PublicAddress
        |> optional "lineOne" string ""
        |> optional "lineTwo" string ""
        |> optional "zipCode" string ""
        |> required "type" publicAddressType


publicAddressType : Decoder PublicAddressType
publicAddressType =
    stringToEnum
        [ ( CommunityAddress, "community" )
        , ( MailingAddress, "mailing" )
        ]


locationType : Decoder LocationType
locationType =
    stringToEnum
        [ ( Rural, "rural" )
        , ( Urban, "urban" )
        , ( Suburban, "suburban" )
        , ( SmallTown, "small town" )
        , ( SmallTown, "small town or village" )
        , ( Mobile, "mobile" )
        , ( LocationTBD, "to be determined" )
        ]


landStatus : Decoder LandStatus
landStatus =
    stringToEnum
        [ ( NoLand, "we do not have land" )
        , ( UndevelopedLand, "we have raw land" )
        , ( UndevelopedLand, "we have undeveloped land" )
        , ( PermittingLand, "we have land in the permitting or zoning stage" )
        , ( DevelopedLand, "we have land we have developed on" )
        ]


incomeSharing : Decoder IncomeSharing
incomeSharing =
    stringToEnum
        [ ( NoIncomeSharing, "none" )
        , ( NoIncomeSharing, "members have completely independent finances" )
        , ( PartialIncomeSharing, "partial share of income" )
        , ( FullIncomeSharing, "100%" )
        , ( FullIncomeSharing, "all or close to all" )
        , ( FullIncomeSharing, "close to all income" )
        ]


cohousingData : Decoder CohousingData
cohousingData =
    decode CohousingData
        |> maybe "siteStatus" decodeCohousingStatus
        |> maybe "yearCompleted" Decode.int
        |> maybe "housingUnits" Decode.int
        |> maybe "hasSharedBuilding" Decode.bool
        |> maybe "sharedBuildingArea" Decode.int
        |> optional "architect" string ""
        |> optional "developer" string ""
        |> optional "lender" string ""


decodeCohousingStatus : Decoder CohousingStatus
decodeCohousingStatus =
    stringToEnum
        [ ( CohousingBuilding, "building" )
        , ( CohousingCompleted, "completed" )
        , ( CohousingDisbanded, "disbanded" )
        , ( CohousingForming, "forming" )
        , ( CohousingOwnSite, "own site" )
        , ( CohousingRetrofitting, "retrofitting" )
        , ( CohousingSeekingSite, "seeking site" )
        , ( CohousingSiteOptioned, "site optioned" )
        , ( CohousingUnknown, "unknown" )
        ]


imageData : Decoder ImageData
imageData =
    decode ImageData
        |> optional "thumbnailUrl" (Decode.map Just string) Nothing
        |> required "imageUrl" string


coords : Decoder Coords
coords =
    decode Coords
        |> required "latitude" Decode.float
        |> required "longitude" Decode.float



-- Misc Helpers


singleton : Decoder a -> Decoder (List a)
singleton =
    Decode.andThen (List.singleton >> Decode.succeed)


oneOrList : Decoder a -> Decoder (List a)
oneOrList decoder =
    Decode.oneOf [ singleton decoder, Decode.list decoder ]


date : Decoder Date
date =
    string
        |> Decode.andThen (Date.fromString >> fromResult)


stringToEnum : List ( a, String ) -> Decoder a
stringToEnum conversions =
    let
        convert maps str =
            case maps of
                ( enumType, enumString ) :: cs ->
                    if str == enumString then
                        Ok enumType
                    else
                        convert cs str

                [] ->
                    Err <| "Could not Decode " ++ str
    in
        Decode.string
            |> Decode.andThen (String.toLower >> convert conversions >> fromResult)


fromResult : Result String a -> Decoder a
fromResult result =
    case result of
        Ok a ->
            Decode.succeed a

        Err s ->
            Decode.fail s
