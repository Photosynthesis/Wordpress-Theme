module Decoders exposing (communityDecoder)

import Date exposing (Date)
import Json.Decode as Decode exposing (Decoder, string)
import Json.Decode.Pipeline exposing (decode, required, optional)
import Communities exposing (..)


resultToDecoder : Result String a -> Decoder a
resultToDecoder result =
    case result of
        Ok a ->
            Decode.succeed a

        Err s ->
            Decode.fail s


communityStatusDecoder : Decoder CommunityStatus
communityStatusDecoder =
    let
        decode str =
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
            |> Decode.andThen (String.toLower >> decode >> resultToDecoder)


visitorsWelcomeDecoder : Decoder VisitorsWelcome
visitorsWelcomeDecoder =
    let
        decode str =
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
            |> Decode.andThen (String.toLower >> decode >> resultToDecoder)


membersWelcomeDecoder : Decoder MembersWelcome
membersWelcomeDecoder =
    let
        decode str =
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
            |> Decode.andThen (String.toLower >> decode >> resultToDecoder)


dateDecoder : Decoder Date
dateDecoder =
    string
        |> Decode.andThen (Date.fromString >> resultToDecoder)


stringToEnumDecoder : List ( a, String ) -> Decoder a
stringToEnumDecoder conversions =
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
            |> Decode.andThen (String.toLower >> convert conversions >> resultToDecoder)


communityTypeDecoder : Decoder CommunityType
communityTypeDecoder =
    stringToEnumDecoder
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


singletonDecoder : Decoder a -> Decoder (List a)
singletonDecoder =
    Decode.andThen (List.singleton >> Decode.succeed)


communityDecoder : Decoder Community
communityDecoder =
    decode Community
        |> required "id" (Decode.map CommunityID Decode.int)
        |> required "name" string
        |> required "slug" string
        |> required "imageUrl" (Decode.nullable string)
        |> required "thumbnailUrl" (Decode.nullable string)
        |> required "communityStatus" communityStatusDecoder
        |> optional "city" string ""
        |> optional "state" string ""
        |> optional "country" string ""
        |> required "openToVisitors" visitorsWelcomeDecoder
        |> required "openToMembership" membersWelcomeDecoder
        |> required "communityTypes"
            (Decode.oneOf
                [ singletonDecoder communityTypeDecoder
                , Decode.list communityTypeDecoder
                ]
            )
        |> required "updatedAt" dateDecoder
        |> required "createdAt" dateDecoder
