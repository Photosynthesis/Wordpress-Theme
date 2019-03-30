port module Wholesale exposing (main)

import Browser
import Dict exposing (Dict)
import Html
    exposing
        ( Html
        , a
        , b
        , button
        , div
        , em
        , h1
        , h2
        , h3
        , i
        , img
        , input
        , label
        , li
        , p
        , table
        , tbody
        , td
        , text
        , tfoot
        , th
        , thead
        , tr
        , ul
        )
import Html.Attributes
    exposing
        ( checked
        , class
        , colspan
        , for
        , href
        , id
        , required
        , src
        , step
        , style
        , target
        , type_
        , value
        )
import Html.Events exposing (onCheck, onInput, onSubmit)
import Http
import Json.Decode as Decode exposing (Value)
import Json.Encode as Encode


main : Program () Model Msg
main =
    Browser.element
        { init = always init
        , update = update
        , view = view
        , subscriptions = always <| stripeTokenReceived StripeTokenReceived
        }



-- PORTS


port collectStripeToken : ( String, Int ) -> Cmd msg


port stripeTokenReceived : ({ token : String, checkoutArgs : Value } -> msg) -> Sub msg



-- MODEL


type alias Model =
    { communitiesMagazineQuantity : String
    , bestOfCommunitiesSetQuantity : String
    , quantityBySlug : SlugQuantities
    , businessName : String
    , contactName : String
    , phoneNumber : String
    , emailAddress : String
    , sendProductEmails : Bool
    , responseStatus : ResponseStatus
    , formError : String
    }


init : ( Model, Cmd Msg )
init =
    ( { communitiesMagazineQuantity = ""
      , bestOfCommunitiesSetQuantity = ""
      , quantityBySlug = Dict.empty
      , businessName = ""
      , contactName = ""
      , phoneNumber = ""
      , emailAddress = ""
      , sendProductEmails = True
      , responseStatus = NotAsked
      , formError = ""
      }
    , Cmd.none
    )


type alias SlugQuantities =
    Dict String String


getQuantity : SlugQuantities -> String -> Maybe Int
getQuantity quantities slug =
    Dict.get slug quantities
        |> Maybe.andThen String.toInt


type Cents
    = Cents Int


centsAdd : Cents -> Cents -> Cents
centsAdd (Cents c1) (Cents c2) =
    Cents <| c1 + c2


toDollars : Cents -> String
toDollars (Cents i) =
    let
        wholePart =
            i // 100

        fractional =
            remainderBy 100 i

        fractionalString =
            case String.length (String.fromInt fractional) of
                0 ->
                    "00"

                1 ->
                    "0" ++ String.fromInt fractional

                2 ->
                    String.fromInt fractional

                _ ->
                    String.left 2 <| String.fromInt fractional
    in
    String.fromInt wholePart ++ "." ++ fractionalString


type ResponseStatus
    = NotAsked
    | Loading
    | Error
    | Success


type alias SingleProduct =
    { name : String
    , slug : String
    , thumbnail : String
    , msrp : String
    , description : Html Msg
    , pricePerUnit : Cents
    }


singleProductTotal : SingleProduct -> Int -> Cents
singleProductTotal { pricePerUnit } quantity =
    (\(Cents c) -> Cents <| c * quantity) pricePerUnit


type alias ProductSet =
    { name : String
    , thumbnail : String
    , msrp : String
    , description : String
    , pricePerItem : Cents
    , setPrice : Cents
    , items : List ProductSetItem
    }


type alias ProductSetItem =
    { name : String
    , slug : String
    }


{-| The total for a ProductSet - it's sub-items & the entire set subtotal.
-}
productSetTotal : ProductSet -> Int -> SlugQuantities -> Cents
productSetTotal productSet setQuantity quantities =
    List.foldl (\i acc -> centsAdd acc <| productSetItemTotal quantities productSet.pricePerItem i)
        (Cents 0)
        productSet.items
        |> (\c -> centsAdd c <| productSetSubTotal productSet setQuantity)


{-| The sub total for the ProductSet's "Entire Set" option.
-}
productSetSubTotal : ProductSet -> Int -> Cents
productSetSubTotal { setPrice } quantity =
    (\(Cents p) -> Cents <| p * quantity) setPrice


{-| The total for a ProductSetItem
-}
productSetItemTotal : SlugQuantities -> Cents -> ProductSetItem -> Cents
productSetItemTotal quantities pricePerItem { slug } =
    getQuantity quantities slug
        |> Maybe.map ((\(Cents price) q -> Cents <| price * q) pricePerItem)
        |> Maybe.withDefault (Cents 0)


type alias VolumeDiscountProduct =
    { name : String
    , thumbnail : String
    , url : String
    , description : List String
    , priceTiers :
        List
            { minQuantity : Int
            , price : Cents
            }
    }


{-| Return the total Price for a given Quantity of Volume-Discounted Products.
-}
volumeDiscountTotal : VolumeDiscountProduct -> Int -> Cents
volumeDiscountTotal product quantity =
    volumeDiscountUnitPrice product quantity
        |> (\(Cents price) -> Cents <| quantity * price)


{-| Return the Unit Price for a Volume-Discounted Product, given a Quantity.
-}
volumeDiscountUnitPrice : VolumeDiscountProduct -> Int -> Cents
volumeDiscountUnitPrice { priceTiers } quantity =
    let
        getPriceTier tiers =
            case tiers of
                { price } :: [] ->
                    price

                { price } :: nextTier :: ts ->
                    if quantity < nextTier.minQuantity then
                        price

                    else
                        getPriceTier (nextTier :: ts)

                [] ->
                    Cents 0
    in
    getPriceTier priceTiers


calculateTotals : Model -> { total : Cents, subTotal : Cents, shippingTotal : Cents, shippingQuantity : Int }
calculateTotals model =
    let
        total =
            centsAdd subTotal shippingTotal

        subTotal =
            singleProductSubTotal
                |> centsAdd setSubTotal
                |> centsAdd magazineSubTotal

        singleProductSubTotal =
            allSingleProducts
                |> List.map
                    (\sp ->
                        Dict.get sp.slug model.quantityBySlug
                            |> Maybe.andThen String.toInt
                            |> Maybe.withDefault 0
                            |> singleProductTotal sp
                    )
                |> List.foldl centsAdd (Cents 0)

        setSubTotal =
            productSetTotal bestOfCommunities bestOfSetQuantity model.quantityBySlug

        magazineSubTotal =
            volumeDiscountTotal communitiesMagazine magazineQuantity

        shippingTotal =
            shippingQuantity
                |> (*) 100
                |> Cents

        -- Magazine Subscriptions Get Free Shipping
        shippingQuantity =
            singleProductShippingQuantity
                + wisdomSetAdditionalShippingQuantity
                + bestOfSetQuantity
                + bestOfQuantities

        singleProductShippingQuantity =
            allSingleProducts
                |> List.filterMap
                    (\sp ->
                        Dict.get sp.slug model.quantityBySlug
                            |> Maybe.andThen String.toInt
                    )
                |> List.sum

        -- These should count as 4 for shipping so we add the extra 3
        wisdomSetAdditionalShippingQuantity =
            Dict.get wisdomSet.slug model.quantityBySlug
                |> Maybe.andThen String.toInt
                |> Maybe.map ((*) 3)
                |> Maybe.withDefault 0

        bestOfQuantities =
            bestOfCommunities.items
                |> List.filterMap
                    (\setItem ->
                        Dict.get setItem.slug model.quantityBySlug
                            |> Maybe.andThen String.toInt
                    )
                |> List.sum

        bestOfSetQuantity =
            parseIntOrZero model.bestOfCommunitiesSetQuantity

        magazineQuantity =
            parseIntOrZero model.communitiesMagazineQuantity
    in
    { total = total
    , subTotal = subTotal
    , shippingTotal = shippingTotal
    , shippingQuantity = shippingQuantity
    }



-- UPDATE


type Msg
    = UpdateQuantityWithSlug String String
    | UpdateSetQuantity String
    | UpdateMagazineQuantity String
    | UpdateBusinessName String
    | UpdateContactName String
    | UpdatePhoneNumber String
    | UpdateEmailAddress String
    | UpdateSendingEmails Bool
    | PayButtonClicked
    | StripeTokenReceived { token : String, checkoutArgs : Value }
    | OrderProcessed (Result Http.Error String)


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        UpdateQuantityWithSlug slug newQuantity ->
            ( { model | quantityBySlug = Dict.insert slug newQuantity model.quantityBySlug }
            , Cmd.none
            )

        UpdateSetQuantity newQuantity ->
            ( { model | bestOfCommunitiesSetQuantity = newQuantity }, Cmd.none )

        UpdateMagazineQuantity newQuantity ->
            ( { model | communitiesMagazineQuantity = newQuantity }, Cmd.none )

        UpdateBusinessName newName ->
            ( { model | businessName = newName }, Cmd.none )

        UpdateContactName newName ->
            ( { model | contactName = newName }, Cmd.none )

        UpdatePhoneNumber newNumber ->
            ( { model | phoneNumber = newNumber }, Cmd.none )

        UpdateEmailAddress newEmail ->
            ( { model | emailAddress = newEmail }, Cmd.none )

        UpdateSendingEmails newStatus ->
            ( { model | sendProductEmails = newStatus }, Cmd.none )

        PayButtonClicked ->
            let
                ( Cents cartTotal, shippingQuantity ) =
                    calculateTotals model |> (\ts -> ( ts.total, ts.shippingQuantity ))
            in
            if shippingQuantity >= 10 then
                ( { model | formError = "" }, collectStripeToken ( model.emailAddress, cartTotal ) )

            else
                ( { model | formError = "A minimum of 10 items is required." }, Cmd.none )

        StripeTokenReceived { token, checkoutArgs } ->
            ( { model | responseStatus = Loading }, placeOrder token checkoutArgs model )

        OrderProcessed (Ok response) ->
            let
                updatedModel =
                    if response == "ok" then
                        { model | responseStatus = Success, formError = "" }

                    else
                        { model | responseStatus = Error, formError = response }
            in
            ( updatedModel, Cmd.none )

        OrderProcessed (Err httpError) ->
            let
                updatedModel =
                    case httpError of
                        Http.BadUrl _ ->
                            { model | formError = "Invalid form URL - please contact bookstore@ic.org." }

                        Http.Timeout ->
                            { model | formError = "Did not receive a response from the server. Please try again later." }

                        Http.NetworkError ->
                            { model | formError = "Could not reach server, please check your internet connection." }

                        Http.BadStatus r ->
                            { model | formError = "Received error response from server(" ++ String.fromInt r.status.code ++ ") - please contact bookstore@ic.org." }

                        Http.BadPayload errorMessage _ ->
                            { model | formError = "Received unexpected response from server(" ++ errorMessage ++ ") - please contact bookstore@ic.org." }
            in
            ( updatedModel, Cmd.none )


placeOrder : String -> Value -> Model -> Cmd Msg
placeOrder stripeToken checkoutArgs model =
    let
        encodeStringToInt =
            parseIntOrZero >> Encode.int

        requestBody =
            Http.jsonBody <|
                Encode.object
                    [ ( "stripeToken", Encode.string stripeToken )
                    , ( "checkoutArgs", checkoutArgs )
                    , ( "businessName", Encode.string model.businessName )
                    , ( "contactName", Encode.string model.contactName )
                    , ( "phoneNumber", Encode.string model.phoneNumber )
                    , ( "emailAddress", Encode.string model.emailAddress )
                    , ( "sendEmails", Encode.bool model.sendProductEmails )
                    , ( "magazineQuantity", encodeStringToInt model.communitiesMagazineQuantity )
                    , ( "bestOfSetQuantity", encodeStringToInt model.bestOfCommunitiesSetQuantity )
                    , ( "slugQuantities"
                      , Encode.object <|
                            List.map (\( s, q ) -> ( s, encodeStringToInt q )) <|
                                Dict.toList model.quantityBySlug
                      )
                    ]

        responseDecoder =
            Decode.field "status" Decode.string
    in
    Http.post "/wp-json/v1/wholesale/checkout/" requestBody responseDecoder
        |> Http.send OrderProcessed



-- VIEW


view : Model -> Html Msg
view model =
    let
        { total, subTotal, shippingTotal } =
            calculateTotals model
    in
    if model.responseStatus == Success then
        div [ class "alert alert-success mx-4" ]
            [ p []
                [ b [] [ i [ class "fa fa-check-circle fa-2x" ] [] ]
                , text " Thanks for your order! We've sent you a confirmation email with a summary of your order and will contact you when your order has shipped."
                ]
            ]

    else
        Html.form [ onSubmit PayButtonClicked ] <|
            [ h1 [] [ text "Wholesale Order Form" ]
            , table [ class "table" ]
                [ thead []
                    [ tr []
                        [ th [] [ text "" ]
                        , th [ class "text-right" ] [ text "Quantity" ]
                        , th [ class "text-right" ] [ text "Price per Unit" ]
                        , th [ class "text-right" ] [ text "Product Total" ]
                        ]
                    ]
                , tbody [] <|
                    List.concatMap (renderSingleProduct model.quantityBySlug) allSingleProducts
                        ++ List.concat
                            [ renderVolumeDiscountProduct communitiesMagazine
                                model.communitiesMagazineQuantity
                            , renderProductSet bestOfCommunities
                                model.bestOfCommunitiesSetQuantity
                                model.quantityBySlug
                            ]
                , tfoot [ class "font-weight-bold text-right" ]
                    [ tr []
                        [ td [ colspan 3, class "text-right" ] [ text "Sub-Total:" ]
                        , td [] [ text <| "$" ++ toDollars subTotal ]
                        ]
                    , tr []
                        [ td [ colspan 3, class "text-right" ] [ text "Shipping:" ]
                        , td [] [ text <| "$" ++ toDollars shippingTotal ]
                        ]
                    , tr []
                        [ td [ colspan 3, class "text-right" ] [ text "Total:" ]
                        , td [] [ text <| "$" ++ toDollars total ]
                        ]
                    ]
                ]
            , additionalFields model
            , p [ class "text-right" ]
                [ div [ class "d-inline-block mr-4 text-danger" ] [ b [] [ text model.formError ] ]
                , button
                    [ class "btn btn-primary btn-lg", type_ "submit" ]
                  <|
                    if model.responseStatus == Loading then
                        [ i [ class "fa fa-spinner fa-spin fa-2x pull-left" ] []
                        , text " Processing Order..."
                        ]

                    else
                        [ text "Pay with Credit Card" ]
                ]
            ]


additionalFields : Model -> Html Msg
additionalFields model =
    let
        inputClasses =
            "form-control col-8 col-md-6"

        fieldLabel inputId labelText =
            label [ class "col-16 col-md-18 col-form-label text-right font-weight-bold", for inputId ]
                [ text <| labelText ++ ": " ]
    in
    div []
        [ div [ class "form-group row" ]
            [ fieldLabel "email" "Email Address"
            , input
                [ type_ "email"
                , id "email"
                , class inputClasses
                , value model.emailAddress
                , onInput UpdateEmailAddress
                , required True
                ]
                []
            ]
        , div [ class "form-group row" ]
            [ fieldLabel "business-name" "Business Name"
            , input
                [ type_ "text"
                , id "business-name"
                , class inputClasses
                , value model.businessName
                , onInput UpdateBusinessName
                , required True
                ]
                []
            ]
        , div [ class "form-group row" ]
            [ fieldLabel "contact-name" "Contact Name"
            , input
                [ type_ "text"
                , id "contact-name"
                , class inputClasses
                , value model.contactName
                , onInput UpdateContactName
                , required True
                ]
                []
            ]
        , div [ class "form-group row" ]
            [ fieldLabel "phone-number" "Phone Number"
            , input
                [ type_ "tel"
                , id "phone-number"
                , class inputClasses
                , value model.phoneNumber
                , onInput UpdatePhoneNumber
                , required True
                ]
                []
            ]
        , div [ class "form-group row" ]
            [ fieldLabel "send-email" "Should we contact you with new wholesale products or sales?"
            , input
                [ type_ "checkbox"
                , id "send-email"
                , class "col-8 col-md-6"
                , checked model.sendProductEmails
                , onCheck UpdateSendingEmails
                ]
                []
            ]
        ]


renderSingleProduct : SlugQuantities -> SingleProduct -> List (Html Msg)
renderSingleProduct quantities ({ name, slug, msrp, description, pricePerUnit } as product) =
    let
        maybeQuantity =
            getQuantity quantities slug

        productTotal =
            maybeQuantity
                |> Maybe.map (singleProductTotal product >> (\c -> "$" ++ toDollars c))
                |> Maybe.withDefault ""

        inputValue =
            maybeQuantity
                |> Maybe.map (\q -> [ value <| String.fromInt q ])
                |> Maybe.withDefault []
    in
    [ tr []
        [ td [ colspan 4, class "pt-4" ]
            [ h2 []
                [ b []
                    [ a
                        [ href <| "/community-bookstore/product/" ++ slug ++ "/"
                        , target "_blank"
                        ]
                        [ text name ]
                    ]
                ]
            , div [ class "clearfix px-4 mr-4" ]
                [ img [ src product.thumbnail, class "pull-left mb-1 mr-3", style "max-width" "25%" ] []
                , em [] [ text <| "Suggested Retail Price: $" ++ msrp ]
                , description
                ]
            ]
        ]
    , tr []
        [ td [ class "border-top-0" ] []
        , td [ class "text-right border-top-0 pb-4" ]
            [ input
                ([ class "form-control"
                 , type_ "number"
                 , Html.Attributes.min "0"
                 , step "1"
                 , onInput <| UpdateQuantityWithSlug slug
                 ]
                    ++ inputValue
                )
                []
            ]
        , td [ class "text-right border-top-0" ] [ b [] [ text <| "x $" ++ toDollars pricePerUnit ] ]
        , td [ class "text-right border-top-0" ] [ b [] [ text <| productTotal ] ]
        ]
    ]


renderProductSet : ProductSet -> String -> SlugQuantities -> List (Html Msg)
renderProductSet ({ name, msrp, pricePerItem, setPrice, items } as pSet) setQuantity quantities =
    let
        maybeSetQuantity =
            String.toInt setQuantity

        setInputValue =
            maybeSetQuantity
                |> Maybe.map (\q -> [ value <| String.fromInt q ])
                |> Maybe.withDefault []

        setTotal =
            maybeSetQuantity
                |> Maybe.map (\q -> "$" ++ toDollars (productSetSubTotal pSet q))
                |> Maybe.withDefault ""
                |> text

        renderedItems =
            List.map renderProductSetItem items

        renderProductSetItem ({ slug } as item) =
            let
                inputValue =
                    getQuantity quantities slug
                        |> Maybe.map (\q -> [ value <| String.fromInt q ])
                        |> Maybe.withDefault []

                total =
                    productSetItemTotal quantities pricePerItem item
                        |> (\((Cents c) as cents) ->
                                if c > 0 then
                                    "$" ++ toDollars cents

                                else
                                    ""
                           )
            in
            tr []
                [ td []
                    [ b []
                        [ a [ href <| "/community-bookstore/product/" ++ slug ++ "/", target "_blank" ]
                            [ text item.name ]
                        ]
                    ]
                , td []
                    [ input
                        ([ class "form-control"
                         , type_ "number"
                         , Html.Attributes.min "0"
                         , step "1"
                         , onInput <| UpdateQuantityWithSlug slug
                         ]
                            ++ inputValue
                        )
                        []
                    ]
                , td [ class "text-right" ] [ b [] [ text <| " x $" ++ toDollars pricePerItem ] ]
                , td [ class "text-right" ] [ b [] [ text <| total ] ]
                ]
    in
    tr []
        [ td [ colspan 4, class "pt-4" ]
            [ h2 [] [ b [] [ text name ] ]
            , div [ class "clearfix px-4 mr-4" ]
                [ img [ src pSet.thumbnail, class "pull-left mr-3 mb-1", style "max-width" "25%" ] []
                , em [] [ text <| "Suggested Retail Price: $" ++ msrp ++ " Each" ]
                , p [ class "mr-4" ] [ text pSet.description ]
                ]
            ]
        ]
        :: tr []
            [ td [] [ b [] [ text "Order as an Entire Set:" ] ]
            , td [ class "text-right pb-4" ]
                [ input
                    ([ class "form-control"
                     , type_ "number"
                     , Html.Attributes.min "0"
                     , step "1"
                     , onInput UpdateSetQuantity
                     ]
                        ++ setInputValue
                    )
                    []
                ]
            , td [ class "text-right" ]
                [ b [] [ text <| "x $" ++ toDollars setPrice ] ]
            , td [ class "text-right" ] [ b [] [ setTotal ] ]
            ]
        :: renderedItems


renderVolumeDiscountProduct : VolumeDiscountProduct -> String -> List (Html Msg)
renderVolumeDiscountProduct ({ name, thumbnail, url, description, priceTiers } as product) quantity =
    let
        describeVolumeDiscount =
            ul [] <| List.map (describeTier >> List.singleton >> li []) priceTiers

        describeTier { minQuantity, price } =
            case minQuantity of
                0 ->
                    text <| "1-2 magazines - $" ++ toDollars price

                3 ->
                    text <| "3-5 magazines - 30% off - $" ++ toDollars price

                6 ->
                    text <| "6-10 magazines - 35% off - $" ++ toDollars price

                11 ->
                    text <| "11-15 magazines - 40% off - $" ++ toDollars price

                _ ->
                    text <| "16+ magazines - 50% off - $" ++ toDollars price

        currentTier =
            volumeDiscountUnitPrice product (parseIntOrZero quantity)

        productTotal =
            String.toInt quantity
                |> Maybe.map (volumeDiscountTotal product >> (\s -> "$" ++ toDollars s))
                |> Maybe.withDefault ""
                |> text
    in
    [ tr []
        [ td [ colspan 4, class "pt-4" ]
            [ h3 []
                [ b []
                    [ a [ href url, target "_blank" ] [ text name ]
                    ]
                ]
            , div [ class "clearfix px-4 mr-4" ]
                [ img [ src thumbnail, class "pull-left mr-3 mb-1", style "max-width" "25%" ] []
                , div [] <| List.map (text >> List.singleton >> p []) description
                , describeVolumeDiscount
                ]
            ]
        ]
    , tr []
        [ td [ class "border-top-0" ] []
        , td [ class "text-right border-top-0 pb-4" ]
            [ input
                [ class "form-control"
                , type_ "number"
                , Html.Attributes.min "0"
                , step "1"
                , value quantity
                , onInput UpdateMagazineQuantity
                ]
                []
            ]
        , td [ class "text-right border-top-0" ] [ b [] [ text <| "x $" ++ toDollars currentTier ] ]
        , td [ class "text-right border-top-0" ] [ b [] [ productTotal ] ]
        ]
    ]


{-| Parse an Integer from a String, falling back to `0` on failure.
-}
parseIntOrZero : String -> Int
parseIntOrZero =
    String.toInt >> Maybe.withDefault 0



-- DATA


allSingleProducts : List SingleProduct
allSingleProducts =
    [ wisdomVolumeOne
    , wisdomVolumeTwo
    , wisdomVolumeThree
    , wisdomVolumeFour
    , wisdomSet
    , communitiesDirectory
    , togetherResilient
    , groupFacilitation
    , unitedJudgement
    , aNewWe
    , withinReach
    ]


wisdomVolumeOne : SingleProduct
wisdomVolumeOne =
    { name = "Wisdom of Communities: Volume 1 - Starting a Community"
    , slug = "starting-a-community"
    , thumbnail = "/wp-content/uploads/2018/01/Starting_A_Community.jpg"
    , msrp = "30"
    , pricePerUnit = Cents 1800
    , description =
        div []
            [ p [] [ text "Resources and Stories about Creating and Exploring Intentional Community: It is estimated that currently just 10 percent of new communities move past the initial stages. This book aims to increase the survival rate to successful starts. Volume I includes both general articles and on-the-ground stories from intentional community founders and other catalysts of cooperative efforts " ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            ]
    }


wisdomVolumeTwo : SingleProduct
wisdomVolumeTwo =
    { name = "Wisdom of Communities: Volume 2 - Finding a Community"
    , slug = "finding-a-community"
    , thumbnail = "/wp-content/uploads/2018/02/Wisdom2.png"
    , msrp = "30"
    , pricePerUnit = Cents 1800
    , description =
        div []
            [ p [] [ text "Resources and Stories about Seeking and Joining Intentional Community: Many searches for intentional community fizzle out due to lack of adequate information, guidance, or exposure to fellow travelers’ stories. Authors share experiences, tools, advice, and perspectives that should help anyone searching for an intentional community—whether to visit or to live in—increase the likelihood of finding what they’re seeking." ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            ]
    }


wisdomVolumeThree : SingleProduct
wisdomVolumeThree =
    { name = "Wisdom of Communities: Volume 3 - Communication in Community"
    , slug = "communication-in-community"
    , thumbnail = "/wp-content/uploads/2018/02/Wisdom3-e1519323269676.png"
    , msrp = "30"
    , pricePerUnit = Cents 1800
    , description =
        div []
            [ p [] [ text "Resources and Stories about the Human Dimension of Cooperative Culture: Volume 3 includes articles about decision-making, governance, power, gender, class, race, relationships, intimacy, politics, and neighbor relations in cooperative group culture. These areas are key for communities to address if they are to retain members and develop strong and healthy group connection." ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            ]
    }


wisdomVolumeFour : SingleProduct
wisdomVolumeFour =
    { name = "Wisdom of Communities: Volume 4 - Sustainability in Community"
    , slug = "sustainability-in-community"
    , thumbnail = "/wp-content/uploads/2018/01/Wisdom-4-Sustainability-in-Community-Front-Cover-600x774.png"
    , msrp = "30"
    , pricePerUnit = Cents 1800
    , description =
        div []
            [ p [] [ text "Resources and Stories about Creating Eco-Resilience in Intentional Community: We focus on food, water, shelter, energy, land, permaculture, ecovillage design, eco-education, and resilience in cooperative culture. These areas will prove more and more essential in allowing communities to navigate changing circumstances on our planet, while growing into new, regenerative ways of living and thriving together." ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            ]
    }


wisdomSet : SingleProduct
wisdomSet =
    { name = "Pre-Order — Wisdom of Communities: 4-Volume Set"
    , slug = "wisdom-of-communities-volumes-1-2-3-4-complete-set"
    , thumbnail = "/wp-content/uploads/2018/11/WOCsetImage-600x687.jpg"
    , msrp = "30 / book"
    , pricePerUnit = Cents 7200
    , description =
        div []
            [ p []
                [ text "Since 1972, "
                , i [] [ text "Communities" ]
                , text " magazine, published by the Fellowship for Intentional Community, has been collecting and disseminating the lessons learned, and now we’re distilling them into a new 4 volume book series. Each book is over 300 pages and features over 100 of our best articles. This series is intended to aid community founders, seekers, current communitarians, students, and researchers alike in their explorations."
                ]
            , ul [ class "list-unstyled" ]
                [ li [] [ text "Volume 1: Starting a Community" ]
                , li [] [ text "Volume 2: Finding a Community" ]
                , li [] [ text "Volume 3: Communication in Community" ]
                , li [] [ text "Volume 4: Sustainability in Community" ]
                ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            ]
    }


communitiesDirectory : SingleProduct
communitiesDirectory =
    { name = "Communities Directory, 7th Edition"
    , slug = "communities-directory-book-new-7th-edition"
    , thumbnail = "/wp-content/uploads/2016/01/Screen-Shot-2016-02-18-at-11.25.55-PM-300x387.png"
    , msrp = "30"
    , pricePerUnit = Cents 1800
    , description =
        div []
            [ p [] [ text "For over 20 years we have offered the leading directory for connecting seekers, dreamers and builders with forming and established intentional communities. In our 7th edition, you'll find over 1,200 communities, full-page maps showing where communities are located, charts that compare communities by more than 30 different qualities, and an easy index to find communities interested in specific pursuits." ]
            , p [] [ text "Published by Fellowship for Intentional Community" ]
            , ul [ class "list-unstyled" ]
                [ li [] [ text "ISBN: 978-0971826496" ]
                , li [] [ text "paperbound" ]
                , li [] [ text "8.5 x 1.4 x 11 inches" ]
                , li [] [ text "608 pages" ]
                ]
            ]
    }


togetherResilient : SingleProduct
togetherResilient =
    { name = "Together Resilient: Building Community in the Age of Climate Disruption"
    , slug = "together-resilient-building-community"
    , thumbnail = "/wp-content/uploads/2017/03/Together-Resilient-300x454.png"
    , msrp = "15"
    , pricePerUnit = Cents 900
    , description =
        div []
            [ p [] [ text "Real hope comes from looking unflinchingly at our current circumstances and then committing wholeheartedly to creative action. Never has that been more urgently needed than right now, with the climate crisis looming larger every day. From small solutions to the full re-invention of the systems we find ourselves in, this book mixes anecdote with data-based research to bring you a wide range of options that all embody compassion, creativity, and cooperation. Together Resilient is a book that advocates for citizen-led, community-based action first and foremost while also looking at intentional community as a model for a low carbon future." ]
            , p [] [ text "By Maikwe Ludwig" ]
            , ul [ class "list-unstyled" ]
                [ li [] [ text "ISBN: 978-0971826472" ]
                , li [] [ text "paperbound" ]
                , li [] [ text "6 x 0.4 x 9 inches" ]
                , li [] [ text "166 pages" ]
                ]
            ]
    }


groupFacilitation : SingleProduct
groupFacilitation =
    { name = "A Manual for Group Facilitation"
    , slug = "a-manual-for-group-facilitators"
    , thumbnail = "/wp-content/uploads/imported/manual-for-group-facilitators-l.jpg"
    , msrp = "10"
    , pricePerUnit = Cents 600
    , description =
        div []
            [ p [] [ text "Created by The Center for Conflict Resolution, this helpful book is an informal outline detailing useful and effective techniques to help groups work well. More than a simple “how to,” the manual contains a discussion of the values, dynamics, and common sense behind group process that have been verified by our own experience." ]
            , p [] [ text "Authored by Brian Auvine, Betsy Densmore, Mary Extrom, Scott Poole, Michel Shanklin" ]
            , ul [ class "list-unstyled" ]
                [ li [] [ text "ISBN: 0-9718264-0-4" ]
                , li [] [ text "paperbound" ]
                , li [] [ text "8.5 x 11 inches" ]
                , li [] [ text "89 pages" ]
                ]
            ]
    }


unitedJudgement : SingleProduct
unitedJudgement =
    { name = "Building United Judgment"
    , slug = "building-united-judgment"
    , thumbnail = "/wp-content/uploads/imported/building-united-judgment-rescan-300x393.jpg"
    , msrp = "10"
    , pricePerUnit = Cents 600
    , description =
        div []
            [ p [] [ text "Building United Judgment describes the techniques and skills which groups can apply to make the principles of consensus work effectively. Whether you are new to consensus or a “practiced hand,” whether your group uses consensus in the “classic” form or wants to apply consensus principles to your own decision making structure, this book provides a thorough review of practical methods that can make your efforts work. Also created by The Center for Conflict Resolution." ]
            , p [] [ text "Authored by Michel Avery, Barbara Streibel, Brian Auvine, Lonnie Weiss" ]
            , ul [ class "list-unstyled" ]
                [ li [] [ text "ISBN: 978-0-9602714-6-7" ]
                , li [] [ text "paperbound" ]
                , li [] [ text "8 x 11 inches" ]
                , li [] [ text "124 pages" ]
                ]
            ]
    }


aNewWe : SingleProduct
aNewWe =
    { name = "A New We - Ecological Communities in Europe"
    , slug = "ecological-communities-in-europe"
    , thumbnail = "/wp-content/uploads/imported/a-new-we.jpg"
    , msrp = "15"
    , pricePerUnit = Cents 900
    , description =
        div []
            [ p [] [ text "A New We is a two-hour documentary that profiles 10 different European communities with a core commitment to sustainability. In these 10 communities, the creative solutions to many social, environmental and economic challenges exemplify the nearly infinite capacity for human-, community- and self-development. The variety of situations and voices inspires hope for the future of humanity and all life on the planet." ]
            , p [] [ text "Created by by Stefan Wolf." ]
            ]
    }


withinReach : SingleProduct
withinReach =
    { name = "Within Reach - Journey to Find Sustainable Community"
    , slug = "within-reach"
    , thumbnail = "/wp-content/uploads/2014/08/WR_official_poster-300x450.jpg"
    , msrp = "12"
    , pricePerUnit = Cents 720
    , description = p [] [ text "One of the most important questions facing the world today is “Can humans live sustainably?” This film answers this in a resounding way – Yes! Within Reach documents one resilient couple’s 6,500 mile bicycling journey across the United States in search of sustainable communities. Mandy and Ryan gave up their corporate jobs and houses to travel thousands of miles in search of a new home, while also looking within. As you journey along, you’ll meet people from around the country showing that there is a better way to live together on this planet. It is not only possible, it is already underway!" ]
    }


bestOfCommunities : ProductSet
bestOfCommunities =
    { name = "Best of Communities"
    , thumbnail = "/wp-content/uploads/2014/01/best-of-communities-bundle-300x224.png"
    , msrp = "15"
    , description = "In 15 scintillating collections, we have distilled what we consider the most insightful and helpful articles from Communities Magazine and Communities Directory on the topics that you care about most. Compiled for your convenience, discover the very best articles that have appeared over the last 20 years. Each collection is comprised of 15–20 articles, containing a total of 55–65 pages. Published by Fellowship for Intentional Community"
    , pricePerItem = Cents 900
    , setPrice = Cents 13500
    , items =
        [ { name = "Issue I: Intentional Community Overview, and Starting a Community"
          , slug = "intentional-community-overview-and-starting-a-community"
          }
        , { name = "Issue II: Seeking and Visiting a Community"
          , slug = "seeking-and-visiting-community"
          }
        , { name = "Issue III: Leadership, Power, and Membership"
          , slug = "leadership-power-and-membership"
          }
        , { name = "Issue IV: Good Meetings"
          , slug = "good-meetings"
          }
        , { name = "Issue V: Consensus"
          , slug = "consensus"
          }
        , { name = "Issue VI: Agreements, Conflict, and Communication"
          , slug = "agreements-conflict-and-communication"
          }
        , { name = "Issue VII: Relationships, Intimacy, Health, and Well-being"
          , slug = "relationships-intimacy-health-and-well-being"
          }
        , { name = "Issue VIII: Children in Community"
          , slug = "children-in-community"
          }
        , { name = "Issue IX: Community for Elders"
          , slug = "community-for-elders"
          }
        , { name = "Issue X: Sustainable Food, Energy, and Transportation"
          , slug = "sustainable-food-energy-transportation"
          }
        , { name = "Issue XI: Green Building, Ecovillage Design, and Land Preservation"
          , slug = "green-building-ecovillage-design-and-land-preservation"
          }
        , { name = "Issue XII: Cohousing"
          , slug = "cohousing-compilation"
          }
        , { name = "Issue XIII: Cooperative Economics and Creating Community Where You Are"
          , slug = "cooperative-economics-and-creating-community-where-you-are"
          }
        , { name = "Issue XIV: Challenges and Lessons of Community"
          , slug = "challenges-and-lessons-of-community"
          }
        , { name = "Issue XV: The Peripatetic Communitarian: The Best of Geoph Kozeny"
          , slug = "the-peripatetic-communitarian-the-best-of-geoph-kozeny"
          }
        ]
    }


communitiesMagazine : VolumeDiscountProduct
communitiesMagazine =
    { name = "Communities Magazine"
    , thumbnail = "/wp-content/uploads/2013/06/Mag.covers-12up-600x323.jpg"
    , url = "/communities-magazine-home/"
    , description =
        [ "Communities magazine is the primary resource for information, stories, and ideas about intentional communities—including urban co-ops, cohousing groups, ecovillages, and rural communes. Communities also focuses on creating and enhancing community in the workplace, in nonprofit or activist organizations, and in neighborhoods. We explore the joys and challenges of cooperation in its many dimensions, and pass the wisdom on to you and your community. Published by Fellowship for Intentional Community, since 1971"
        , "Selecting quantity “1” offers a full year (4 issues) subscription. We are a quarterly publication, shipping the first week of March, June, September, and December. Different rates apply than above. No shipping will be applied."
        ]
    , priceTiers =
        -- Update renderVolumeDiscountProduct when quantities change.
        -- TODO: Add "description" field so renderVolumeDiscountProduct isn't tied to this function
        [ { minQuantity = 0, price = Cents 794 }
        , { minQuantity = 3, price = Cents 556 }
        , { minQuantity = 6, price = Cents 517 }
        , { minQuantity = 11, price = Cents 477 }
        , { minQuantity = 16, price = Cents 397 }
        ]
    }
