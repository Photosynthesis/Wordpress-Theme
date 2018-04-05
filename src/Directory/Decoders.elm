module Directory.Decoders exposing (community)

import Date exposing (Date)
import Json.Decode as Decode exposing (Decoder, string)
import Json.Decode.Pipeline exposing (decode, required, optional)
import Directory.Communities exposing (..)


community : Decoder Community
community =
    decode Community
        |> required "id" (Decode.map CommunityID Decode.int)
        |> required "name" string
        |> required "slug" string
        |> required "imageUrl" (Decode.nullable string)
        |> required "thumbnailUrl" (Decode.nullable string)
        |> required "communityStatus" communityStatus
        |> optional "city" string ""
        |> optional "state" string ""
        |> optional "country" string ""
        |> required "openToVisitors" visitorsWelcome
        |> required "openToMembership" membersWelcome
        |> required "communityTypes"
            (Decode.oneOf
                [ singleton communityType
                , Decode.list communityType
                ]
            )
        |> required "updatedAt" date
        |> required "createdAt" date


communityStatus : Decoder CommunityStatus
communityStatus =
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
            |> Decode.andThen (String.toLower >> decode >> fromResult)


visitorsWelcome : Decoder VisitorsWelcome
visitorsWelcome =
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
            |> Decode.andThen (String.toLower >> decode >> fromResult)


membersWelcome : Decoder MembersWelcome
membersWelcome =
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
            |> Decode.andThen (String.toLower >> decode >> fromResult)


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


singleton : Decoder a -> Decoder (List a)
singleton =
    Decode.andThen (List.singleton >> Decode.succeed)


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
